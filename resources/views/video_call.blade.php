<html>
<head>
    <title>PeerJS Video Call</title>
</head>
<body>
    <h1>PeerJS Video Call</h1>
    <p id="peer-id">Your peer ID: </p>
    <input type="text" id="peer-id-input" placeholder="Enter peer ID to call">
    <button id="call-button">Call</button>
    <br>
    <video id="remote-video" autoplay></video>

    <video id="localVideo" autoplay></video>


    <script src="https://cdn.jsdelivr.net/npm/peerjs@1.3.2/dist/peerjs.min.js"></script>
    {{--  <script src="https://cdn.peerjs.com/1.3.1/peerjs.min.js"></script>  --}}

    <script src="{{ asset('js/peer.js') }}"></script>
</body>
</html>
