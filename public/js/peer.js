document.addEventListener("DOMContentLoaded", () => {
    const peer = new Peer();

    peer.on('open', function(id) {
        console.log('My peer ID is: ' + id);
    });

    peer.on('call', function(call) {
        // Answer automatically for demo purposes
        call.answer(window.localStream);
        call.on('stream', function(remoteStream) {
            // Show stream in some video/canvas element
            const video = document.querySelector('#remoteVideo');
            video.srcObject = remoteStream;
            video.play();
        });
    });

    document.querySelector('#call-button').addEventListener('click', function() {
        const peerId = document.querySelector('#peer-id-input').value;
        const call = peer.call(peerId, window.localStream);
        call.on('stream', function(remoteStream) {
            // Show stream in some video/canvas element
            const video = document.querySelector('#remoteVideo');
            video.srcObject = remoteStream;
            video.play();
        });
    });
});

navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(function(stream) {
    window.localStream = stream;
    const video = document.querySelector('#localVideo');
    video.srcObject = stream;
    video.play();
}).catch(function(err) {
    console.error('Failed to get local stream', err);
});
