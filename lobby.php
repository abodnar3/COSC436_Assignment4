<!DOCTYPE html>
<html lang="en">

<!-- imports socket io & css -->
<head>
    <link rel="stylesheet" href="./index.css">
    <link rel="stylesheet" href="./lobby.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic-Tac-Toe Lobby</title>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
</head>


<body>
    <!-- structure of the lobby page -->
    <!-- headings -->
    <h1>Tic-Tac-Toe Game</h1>
    <h2>By: Andrew Bodnar & Ryan Hastie</h2>
    <div id="howToPlay-openBtn" class="howToPlay">How to Play</div>

    <!-- how-to-play overlay & button -->
    <div id="howToPlay-overlay" class="howToPlay">
        <div>
            <button id="howToPlay-closeBtn" class="howToPlay">&times;</button>
            <h1>How to Play:</h1>
            <p>
                null
            </p>
        </div>
    </div>

    <!-- players table -->
    <div class="lobby-container">
        <div class="table-box">
            <h3>Players</h3>
            <table id="activeGamesTable">
                <thead>
                    <tr>
                        <th>X Player</th>
                        <th>O Player</th>
                    </tr>
                </thead>
                <tbody id="activeGamesBody">
                    <tr>
                        <td colspan="2">No active games</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- idle clients table -->
        <div class="table-box">
            <h3>Idle Clients</h3>
            <table id="idlePlayersTable">
                <tbody id="idlePlayersBody">
                    <tr>
                        <td>No idle players</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- new-game button -->
    <button id="newGameBtn">New Game</button>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
    
            // shows overlay
            function showOverlay(overlay) {
                overlay.style.display = 'flex';
            }

            // hides overlay
            function hideOverlay(overlay) {
                overlay.style.display = 'none';
            }

            // references html elements
            const howToPlay_overlay = document.getElementById("howToPlay-overlay");
            const howToPlay_openBtn = document.getElementById("howToPlay-openBtn");
            const howToPlay_closeBtn = document.getElementById("howToPlay-closeBtn");

            // sets event listeners for how-to-play overlay
            howToPlay_openBtn.addEventListener('click', () => { showOverlay(howToPlay_overlay); });
            howToPlay_closeBtn.addEventListener('click', () => { hideOverlay(howToPlay_overlay); });

            // connects socket to server
            const socket = io("http://localhost:8080");

            // references html
            const activeGamesBody = document.getElementById("activeGamesBody");
            const idlePlayersBody = document.getElementById("idlePlayersBody");
            const newGameBtn = document.getElementById("newGameBtn");

            // update active games >> resets html table, displays 'no active games ' if the sql table is empty, and
            // otherwise gets all players from the sql table and displays them in the html table
            socket.on("update_active_games", (games) => {
                activeGamesBody.innerHTML = "";
                if (games.length === 0) {
                    activeGamesBody.innerHTML = "<tr><td colspan='2'>No active games</td></tr>";
                    return;
                }
                games.forEach(game => {
                    const row = document.createElement("tr");
                    row.innerHTML = `<td>${game.x_player}</td><td>${game.o_player}</td>`;
                    activeGamesBody.appendChild(row);
                });
            });

            // update idle players >> resets html table, displays 'no idle players' if the sql table is empty, and
            // otherwise gets all players from the sql table and displays them in the html table
            socket.on("update_idle_players", (players) => {
                idlePlayersBody.innerHTML = "";
                if (players.length === 0) {
                    idlePlayersBody.innerHTML = "<tr><td>No idle players</td></tr>";
                    return;
                }
                players.forEach(name => {
                    const row = document.createElement("tr");
                    row.innerHTML = `<td>${name}</td>`;
                    idlePlayersBody.appendChild(row);
                });
            });

            // new-game button functionality
            newGameBtn.addEventListener("click", () => {
                socket.emit("request_new_game");
            });

            // broadcasts 'get lobby status' to the server
            socket.emit("get_lobby_status");
        });
    </script>
</body>

</html>