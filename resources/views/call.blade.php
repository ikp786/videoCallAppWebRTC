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
            cursor: pointer;
        }
        button {
            margin: 10px;
        }
    </style>
</head>
<body>
    <video id="localVideo" autoplay playsinline></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <button id="callButton">Call Peer</button>
    <button id="endCallButton" style="display: none;">End Call</button>
    <button id="muteButton">Mute</button>
    <button id="cameraButton">Turn Camera Off</button>
    <button id="switchCameraButton" style="display: none;">Switch Camera</button>
    <button id="shareScreenButton">Share Screen</button>
    <button id="stopScreenShareButton" style="display: none;">Stop Sharing</button>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.3.2/peer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/peerjs@1.3.2/dist/peerjs.min.js"></script>
    <script>
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const callButton = document.getElementById('callButton');
        const endCallButton = document.getElementById('endCallButton');
        const muteButton = document.getElementById('muteButton');
        const cameraButton = document.getElementById('cameraButton');
        const switchCameraButton = document.getElementById('switchCameraButton');
        const shareScreenButton = document.getElementById('shareScreenButton');
        const stopScreenShareButton = document.getElementById('stopScreenShareButton');

        let localStream;
        let currentCall;
        let isScreenSharing = false;

        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(stream => {
                localVideo.srcObject = stream;
                localStream = stream;
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
                    currentCall = call;
                    endCallButton.style.display = 'inline-block';
                });

                function callPeer(remotePeerId) {
                    const call = peer.call(remotePeerId, stream);
                    call.on('stream', remoteStream => {
                        remoteVideo.srcObject = remoteStream;
                    });
                    currentCall = call;
                    endCallButton.style.display = 'inline-block';
                }

                callButton.addEventListener('click', () => {
                    const remotePeerId = prompt("Enter remote peer ID:");
                    callPeer(remotePeerId);
                });

                endCallButton.addEventListener('click', () => {
                    if (currentCall) {
                        currentCall.close();
                        endCallButton.style.display = 'none';
                    }
                });

                muteButton.addEventListener('click', () => {
                    localStream.getAudioTracks()[0].enabled = !localStream.getAudioTracks()[0].enabled;
                    muteButton.textContent = localStream.getAudioTracks()[0].enabled ? 'Mute' : 'Unmute';
                });

                cameraButton.addEventListener('click', () => {
                    localStream.getVideoTracks()[0].enabled = !localStream.getVideoTracks()[0].enabled;
                    cameraButton.textContent = localStream.getVideoTracks()[0].enabled ? 'Turn Camera Off' : 'Turn Camera On';
                });

                let currentDeviceId = localStream.getVideoTracks()[0].getSettings().deviceId;

                function switchCamera() {
                    navigator.mediaDevices.enumerateDevices().then(devices => {
                        const videoDevices = devices.filter(device => device.kind === 'videoinput');
                        const currentIndex = videoDevices.findIndex(device => device.deviceId === currentDeviceId);
                        const nextIndex = (currentIndex + 1) % videoDevices.length;
                        const nextDeviceId = videoDevices[nextIndex].deviceId;

                        const constraints = {
                            video: { deviceId: { exact: nextDeviceId } },
                            audio: true
                        };

                        navigator.mediaDevices.getUserMedia(constraints).then(newStream => {
                            const newVideoTrack = newStream.getVideoTracks()[0];
                            localStream.removeTrack(localStream.getVideoTracks()[0]);
                            localStream.addTrack(newVideoTrack);
                            localVideo.srcObject = newStream;
                            currentDeviceId = nextDeviceId;

                            if (currentCall) {
                                currentCall.peerConnection.getSenders().forEach(sender => {
                                    if (sender.track.kind === 'video') {
                                        sender.replaceTrack(newVideoTrack);
                                    }
                                });
                            }
                        }).catch(error => {
                            console.error('Error switching camera:', error);
                        });
                    });
                }

                switchCameraButton.addEventListener('click', switchCamera);

                // Check if there are multiple video devices
                navigator.mediaDevices.enumerateDevices().then(devices => {
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');
                    if (videoDevices.length > 1) {
                        switchCameraButton.style.display = 'inline-block';
                    }
                }).catch(error => {
                    console.error('Error enumerating devices:', error);
                });

                function startScreenShare() {
                    navigator.mediaDevices.getDisplayMedia({ video: true }).then(screenStream => {
                        const screenTrack = screenStream.getVideoTracks()[0];
                        const sender = localStream.getVideoTracks()[0];
                        localStream.removeTrack(sender);
                        localStream.addTrack(screenTrack);
                        localVideo.srcObject = localStream;

                        if (currentCall) {
                            currentCall.peerConnection.getSenders().forEach(sender => {
                                if (sender.track.kind === 'video') {
                                    sender.replaceTrack(screenTrack);
                                }
                            });
                        }

                        isScreenSharing = true;
                        shareScreenButton.style.display = 'none';
                        stopScreenShareButton.style.display = 'inline-block';

                        screenTrack.onended = () => {
                            stopScreenShare();
                        };
                    }).catch(error => {
                        console.error('Error sharing screen:', error);
                    });
                }

                function stopScreenShare() {
                    const videoTrack = localStream.getVideoTracks()[0];
                    videoTrack.stop();
                    isScreenSharing = false;
                    navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(stream => {
                        const newVideoTrack = stream.getVideoTracks()[0];
                        localStream.removeTrack(localStream.getVideoTracks()[0]);
                        localStream.addTrack(newVideoTrack);
                        localVideo.srcObject = localStream;

                        if (currentCall) {
                            currentCall.peerConnection.getSenders().forEach(sender => {
                                if (sender.track.kind === 'video') {
                                    sender.replaceTrack(newVideoTrack);
                                }
                            });
                        }
                    }).catch(error => {
                        console.error('Error accessing media devices.', error);
                    });
                    shareScreenButton.style.display = 'inline-block';
                    stopScreenShareButton.style.display = 'none';
                }

                shareScreenButton.addEventListener('click', startScreenShare);
                stopScreenShareButton.addEventListener('click', stopScreenShare);

                function toggleFullScreen(videoElement) {
                    if (!document.fullscreenElement) {
                        if (videoElement.requestFullscreen) {
                            videoElement.requestFullscreen();
                        } else if (videoElement.mozRequestFullScreen) { // Firefox
                            videoElement.mozRequestFullScreen();
                        } else if (videoElement.webkitRequestFullscreen) { // Chrome, Safari and Opera
                            videoElement.webkitRequestFullscreen();
                        } else if (videoElement.msRequestFullscreen) { // IE/Edge
                            videoElement.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.mozCancelFullScreen) { // Firefox
                            document.mozCancelFullScreen();
                        } else if (document.webkitExitFullscreen) { // Chrome, Safari and Opera
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) { // IE/Edge
                            document.msExitFullscreen();
                        }
                    }
                }

                localVideo.addEventListener('click', () => toggleFullScreen(localVideo));
                remoteVideo.addEventListener('click', () => toggleFullScreen(remoteVideo));

                // Keyboard event listeners
                document.addEventListener('keydown', (event) => {
                    switch (event.key) {
                        case 'c': // 'c' for call
                            callButton.click();
                            break;
                        case 'e': // 'e' for end call
                            endCallButton.click();
                            break;
                        case 'm': // 'm' for mute/unmute
                            muteButton.click();
                            break;
                        case 'v': // 'v' for video on/off
                            cameraButton.click();
                            break;
                        case 's': // 's' for switch camera
                            if (switchCameraButton.style.display === 'inline-block') {
                                switchCameraButton.click();
                            }
                            break;
                        case 'S': // 'Shift + S' for screen share
                            if (!isScreenSharing) {
                                shareScreenButton.click();
                            } else {
                                stopScreenShareButton.click();
                            }
                            break;
                        case 'f': // 'f' for full screen
                            if (document.activeElement === localVideo) {
                                toggleFullScreen(localVideo);
                            } else if (document.activeElement === remoteVideo) {
                                toggleFullScreen(remoteVideo);
                            }
                            break;
                        default:
                            break;
                    }
                });
            })
            .catch(error => {
                console.error('Error accessing media devices.', error);
            });
    </script>
</body>
</html>
