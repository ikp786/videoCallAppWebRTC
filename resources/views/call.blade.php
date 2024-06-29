<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call</title>
    <style>
        video {
            width: 45%;
            height: auto;
            margin: 5%;
        }
    </style>
</head>
<body>
    <video id="localVideo" autoplay playsinline></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.3.2/peer.min.js"></script>
    <script src="{{ asset('js/app.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/peerjs@1.3.2/dist/peerjs.min.js"></script>
    {{--  <script src="https://cdn.peerjs.com/1.3.1/peerjs.min.js"></script>  --}}

    <script src="{{ asset('js/peer.js') }}"></script>
    <script>
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');

        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                localVideo.srcObject = stream;
                const peer = new Peer();

                peer.on('open', id => {
                    console.log('My peer ID is: ' + id);
                    // Send this ID to the server to establish the connection
                });

                peer.on('call', call => {
                    call.answer(stream);
                    call.on('stream', remoteStream => {
                        remoteVideo.srcObject = remoteStream;
                    });
                });

                // Call a remote peer when you have their ID
                function callPeer(remotePeerId) {
                    const call = peer.call(remotePeerId, stream);
                    call.on('stream', remoteStream => {
                        remoteVideo.srcObject = remoteStream;
                    });
                }

                // For testing: replace 'REMOTE_PEER_ID' with actual peer ID
                document.getElementById('callButton').addEventListener('click', () => {
                    const remotePeerId = prompt("Enter remote peer ID:");
                    callPeer(remotePeerId);
                });
            })
            .catch(error => {
                console.error('Error accessing media devices.', error);
            });
    </script>
    <button id="callButton">Call Peer</button>
</body>
</html>
