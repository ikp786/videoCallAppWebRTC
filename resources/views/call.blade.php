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
    <button id="callButton">Call Peer</button>
    <button id="endCallButton">End Call</button>
    <button id="muteButton">Mute</button>
    <button id="cameraButton">Camera Off</button>
    <button id="switchCameraButton">Switch Camera</button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.3.2/peer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/peerjs@1.3.2/dist/peerjs.min.js"></script>
    <script src="{{ asset('js/peer.js') }}"></script>
    <script>
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const muteButton = document.getElementById('muteButton');
        const cameraButton = document.getElementById('cameraButton');
        const switchCameraButton = document.getElementById('switchCameraButton');
        const endCallButton = document.getElementById('endCallButton');

        let localStream;
        let currentCall;
        let isMuted = false;
        let isCameraOff = false;
        let currentCamera = 'user'; // front camera

        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                localStream = stream;
                localVideo.srcObject = stream;

                const peer = new Peer();

                peer.on('open', id => {
                    console.log('My peer ID is: ' + id);
                    // Send this ID to the server to establish the connection
                });

                peer.on('call', call => {
                    currentCall = call;
                    call.answer(stream);
                    call.on('stream', remoteStream => {
                        remoteVideo.srcObject = remoteStream;
                    });
                });

                // Call a remote peer when you have their ID
                function callPeer(remotePeerId) {
                    if (currentCall) {
                        currentCall.close();
                    }
                    const call = peer.call(remotePeerId, stream);
                    currentCall = call;
                    call.on('stream', remoteStream => {
                        remoteVideo.srcObject = remoteStream;
                    });
                }

                document.getElementById('callButton').addEventListener('click', () => {
                    const remotePeerId = prompt("Enter remote peer ID:");
                    callPeer(remotePeerId);
                });

                endCallButton.addEventListener('click', () => {
                    if (currentCall) {
                        currentCall.close();
                        currentCall = null;
                        remoteVideo.srcObject = null;
                    }
                });

                muteButton.addEventListener('click', () => {
                    isMuted = !isMuted;
                    localStream.getAudioTracks()[0].enabled = !isMuted;
                    muteButton.textContent = isMuted ? 'Unmute' : 'Mute';
                });

                cameraButton.addEventListener('click', () => {
                    isCameraOff = !isCameraOff;
                    localStream.getVideoTracks()[0].enabled = !isCameraOff;
                    cameraButton.textContent = isCameraOff ? 'Camera On' : 'Camera Off';
                });

                switchCameraButton.addEventListener('click', () => {
                    switchCamera();
                });

                function switchCamera() {
                    localStream.getVideoTracks().forEach(track => track.stop());
                    const constraints = {
                        video: {
                            facingMode: (currentCamera === 'user') ? 'environment' : 'user'
                        },
                        audio: true
                    };
                    navigator.mediaDevices.getUserMedia(constraints)
                        .then(newStream => {
                            localStream = newStream;
                            localVideo.srcObject = newStream;
                            currentCamera = (currentCamera === 'user') ? 'environment' : 'user';
                        });
                }
            })
            .catch(error => {
                console.error('Error accessing media devices.', error);
            });
    </script>
</body>
</html>
