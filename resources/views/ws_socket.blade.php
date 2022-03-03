<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Hello, world!</title>
</head>
<body style="background-color: grey">
<div class="row m-3">
    <div class="col-12">
        <div class="card">
            <div class="input-group mb-3">
                <input id="token_input" type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="basic-addon2">
                <div class="input-group-append">
                    <button onclick="connect()" id="connect_button" class="btn btn-outline-secondary" type="button">Button</button>
                </div>
            </div>
        </div>
        <div class="card">
            <button id="clear_console_button" type="button" class="btn btn-primary" onclick="clearConsole()">Clear</button>
            <div id="messages" class="card" style="overflow: scroll; height: 700px">
            </div>
        </div>
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>

<script>
    let reconnectInterval = null;
    let messages = document.getElementById('messages');

    function connect() {
        let token_input = document.getElementById('token_input').value;
        const url = `ws://185.125.91.22:8080?token=${token_input}`;
        renderMessage(`Connecting to ${url}`, 'red');
        const ws = new WebSocket(url);

        ws.events = new Map();

        ws.onopen = function open() {
            clearTimeout(reconnectInterval);
            renderMessage('Successfully connected', 'green');
            console.log('Connection opened');
        };

        ws.onmessage = function incoming(msgEvent) {
            handleEvent(msgEvent.data);
        };

        ws.onclose = function (errorCode) {
            clearTimeout(reconnectInterval);
            console.log('Connection closed:', errorCode);
            reconnectInterval = setTimeout(connect, 5000);
        };

        ws.events.set('game.updated', function(data) {
            data = toJson(data);

            renderReceived(data, 'black');
        });

        ws.events.set('game.list.updated', function(data) {
            data = toJson(data);

            renderReceived(data, 'black');
        });


        ws.events.set('user.updated', function(data) {
            data = toJson(data);

            renderReceived(data, 'black');
        });

        function handleEvent(data) {
            renderReceived(data);
            let parsed = null;
            try {
                parsed = JSON.parse(data);
            }
            catch (e) {
                console.log('Error:', e);
            }
            if (parsed) {
                if (parsed['event'] && parsed['data']) {
                    if (ws.events.has(parsed['event'])) {
                        ws.events.get(parsed['event'])(parsed['data']);
                    }
                }
            }
        }

        document.getElementById('clear_console_button').onclick = function() {
            messages.innerHTML = '';
        };

    }

    function toJson(data, format = true) {
        return format ? JSON.stringify(data, null, 4) : JSON.stringify(data);
    }

    function renderReceived(message, color) {
        let div = document.createElement('div');
        div.innerHTML =
            `<div>
                <p style="color:darkgreen">Received:<p>
                <pre class="m-2" style="color:${color}">${message}</pre>
                <hr>
            </div>`;

        messages.appendChild(div);
    }

    function renderMessage(message, color) {
        let div = document.createElement('div');
        div.innerHTML =
            `<div>
                <pre class="m-2" style="color:${color}">${message}</pre>
                <hr>
            </div>`;

        messages.appendChild(div);
    }

    function renderSent(message, color) {
        let div = document.createElement('div');
        div.innerHTML =
            `<div>
                <p style="color:darkgreen">Sent:<p>
                <pre class="m-2" style="color:${color}">${message}</pre>
                <hr>
            </div>`;

        messages.appendChild(div);
    }

    function clearConsole() {
        messages.innerHTML = '';
    }
</script>
</body>
</html>
