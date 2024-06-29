import Peer from 'peerjs';

const peer = new Peer();

peer.on('open', (id) => {
    console.log('My peer ID is: ' + id);
});

// Other peer events and logic here
