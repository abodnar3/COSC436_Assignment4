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

    <div id="xo-prompt" style="display: none; margin-top:20px">
        <h3>Pick X or O</h3>
        <button class="xo-choice" data-choice="X">X</button>
        <button class="xo-choice" data-choice="O">O</button>
    </div>

    <!-- GAME SECTION (hidden until PLAY event) -->
    <div id="game-section" style="display:none; margin-top:40px;">
        <h2>Game In Progress</h2>

        <table id="game-info-table">
            <thead>
                <tr>
                    <th>X Player</th>
                    <th>O Player</th>
                    <th>Turn</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="x-label">-</td>
                    <td id="o-label">-</td>
                    <td id="turn-label">-</td>
                </tr>
            </tbody>
        </table>

        <div id="game-board">
            <div class="cell" data-cell="0"></div>
            <div class="cell" data-cell="1"></div>
            <div class="cell" data-cell="2"></div>

            <div class="cell" data-cell="3"></div>
            <div class="cell" data-cell="4"></div>
            <div class="cell" data-cell="5"></div>

            <div class="cell" data-cell="6"></div>
            <div class="cell" data-cell="7"></div>
            <div class="cell" data-cell="8"></div>
        </div>
    </div>


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
                games.forEach((game, row_index) => {
                    const row = document.createElement("tr");
                    const me = localStorage.getItem("screenname");

                    // X cell
                    const xCell = document.createElement("td");
                    if (game.x_player) {
                        xCell.textContent = game.x_player;
                    } else if (me !== game.o_player) {
                        const joinX = document.createElement("button");
                        joinX.textContent = "Join";
                        joinX.addEventListener("click", () => {
                            socket.emit("join_game", { row_index, team: "X", player: me });
                        });
                        xCell.appendChild(joinX);
                    }

                    // O cell
                    const oCell = document.createElement("td");
                    if (game.o_player) {
                        oCell.textContent = game.o_player;
                    } else if (me !== game.x_player) {
                        const joinO = document.createElement("button");
                        joinO.textContent = "Join";
                        joinO.addEventListener("click", () => {
                            socket.emit("join_game", { row_index, team: "O", player: me });
                        });
                        oCell.appendChild(joinO);
                    }

                    row.appendChild(xCell);
                    row.appendChild(oCell);
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

            socket.on("PLAY", ({ x_player, o_player }) => {
                document.getElementById("game-section").style.display = "block";

                document.getElementById("x-label").textContent = x_player;
                document.getElementById("o-label").textContent = o_player;

                document.getElementById("turn-label").textContent = `X: ${x_player}`;

                const me = localStorage.getItem("screenname");
                if (me === x_player || me === o_player) {
                    document.getElementById("newGameBtn").style.display = "none";
                }
            })

            const name = localStorage.getItem("screenname");

            socket.on("connect", () => {
                socket.emit("register", name);
                console.log("Registered after entering lobby:", name, socket.id);
            });

            // new-game >> shows X/O prompt for user (which team to pick)
            newGameBtn.addEventListener("click", () => {
                newGameBtn.style.display = "none";
                document.getElementById("xo-prompt").style.display = "block";
            });

            document.querySelectorAll(".xo-choice").forEach(btn => {
                btn.addEventListener("click", () => {
                    const choice = btn.dataset.choice;

                    const screen_name = localStorage.getItem("screenname");

                    document.getElementById("xo-prompt").style.display = "none";

                    socket.emit("new_game", `NEW-GAME ${screen_name} ${choice}`);
                })
            })


            // broadcasts 'get lobby status' to the server
            socket.emit("get_lobby_status");
        });
    </script>
</body>
</html>