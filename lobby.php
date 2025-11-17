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

    <!--game playing div -->
    <div class="gamePlayDiv">
        <div><!--info div-->
            <table id="gameInfoTable">
                <th>X:</th>
                <th>O:</th>
                <th>Turn:</th>
                <tr>
                    <!--defaults-->
                    <td>No Player</td>
                    <td>No Player</td>
                    <td>N/A</td>
                </tr>
            </table>

        </div>

        <!--result of game space-->
        <h1 id="gameResult"></h1>

        <div><!--Playing space div-->

        <table>
            <tr>
                <td id="00" onclick="makeMove('00')"></td>
                <td id="01" onclick="makeMove('01')"></td>
                <td id="02" onclick="makeMove('02')"></td>
                
            </tr>
            <tr>
                <td id="10" onclick="makeMove('10')"></td>
                <td id="11" onclick="makeMove('11')"></td>
                <td id="12" onclick="makeMove('12')"></td>
                
            </tr>
            <tr>
                <td id="20" onclick="makeMove('20')"></td>
                <td id="21" onclick="makeMove('21')"></td>
                <td id="22" onclick="makeMove('22')"></td>
                
            </tr>
        </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            //---global variables used in making makeMove. update based off of game
            let yourTurn = false;
            let yourSymbol = 'X';
            let screenName ="";

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

            //onclick function for table data
            function makeMove(cell){
                if(yourTurn){
                    this.innerHTML = yourSymbol;
                    yourTurn = false;
                    
                    socket.emit("MOVE", screenName, cell)
                }
                else{
                    alert("It is not you turn yet");
                }

            }

            //response to end game
            socket.on("END-GAME",(winner)=>{
                document.getElementById("gameResult").innerHTML=winner+" has won the game";

                //disable buttons
                for(let i = 0; i<3; i++){
                    for(let j = 0;j<3;j++){
                        document.getElementById(i.toString()+j.toString()).style.pointerEvents = "none";
                    }
                }


                document.getElementById("newGameBtn").style.display = "block";

            })

            //response from a move being made
            socket.on("MOVE", (cell) =>{
                if(yourSymbol=='X')
                    document.getElementById(cell).innerHTML = 'O';
                else
                    document.getElementById(cell).innerHTML = 'X';

                let checkResult = checkGame();
                
                if(checkResult == "X has won"){
                     socket.emit("END-GAME",('X',screenName));
                }
                else if(checkResult == "O has won"){
                     socket.emit("END-GAME",('O',screenName));

                }
                else {
                    socket.emit("END-GAME",('D'));

                }
                

                yourTurn = true;
            })

            //function to check the status of the board
            function checkGame(){
                
                //check if x has won

                //check rows
                for(let i = 0; i < 3; i++){
                    let line = true;
                    for(let j = 0; j < 3; j++){
                      if (document.getElementById(i.toString()+j.toString()).innerHTML == 'O'){
                        line = false;
                      }
                    }
                    if(line == true)
                        return "X has won";
                }

                //check columns
                for(let i = 0; i< 3; i++){
                    let line = true;
                    for(let j = 0; j < 3; j ++){
                         if (document.getElementById(j.toString()+i.toString()).innerHTML == 'O'){
                        line = false;
                      }
                    }
                    if(line == true)
                        return "X has won";
                }

                //check diagonals
                let line = true;
                for(let i = 0; i<3; i++){
                    if (document.getElementById(i.toString()+i.toString()).innerHTML == 'O'){
                        line = false;
                      }
                }
                if(line == true)
                    return "X has won";

               line = true;
               if(document.getElementById("02").innerHTML == 'O'){
                line = false;
               }
               if(document.getElementById("11").innerHTML == 'O'){
                line = false;
               }
               if(document.getElementById("20").innerHTML == 'O'){
                line = false;
               }
               if(line == true){
                    return "X has won";
               }

               //check if draw

              //check rows
              let draw = true;
              let zero = 0;
              for(let i = 0; i < 3; i++){
                let currentSymbol = document.getElementById(i.toString()+zero.toString());
                let possible = true;
                for(let j = 1; j< 3; j++){
                    if(currentSymbol != document.getElementById(i.toString()+j.toString())){
                        possible = false;
                    }
                }
                if(possible == true){
                    draw = false;
                }
              } 

              //check columns
              for(let i = 0; i < 3; i++){
                let currentSymbol = document.getElementById(zero.toString()+i.toString());
                let possible = true;
                for(let j = 1; j< 3; j++){
                    if(currentSymbol != document.getElementById(j.toString()+i.toString())){
                        possible = false;
                    }
                }
                if(possible == true){
                    draw = false;
                }
              } 

              //check diagonals
              let currentSymbol = document.getElementById(zero.toString()+zero.toString());
              let possible = true;
              for(let i = 0; i< 3; i++){
                if(currentSymbol != document.getElementById(i.toString()+i.toString())){
                        possible = false;
                    }
              }
              if(possible == true){
                    draw = false;
              }

               currentSymbol = document.getElementById("02");
               possible = true;
               if(document.getElementById("11").innerHTML != currentSymbol){
                possible = false;
               }
               if(document.getElementById("20").innerHTML != currentSymbol){
                possible = false;
               }
               if(possible == true){
                    draw = false;
               }

               if(draw == true){
                return "The game is a draw";
               }

               //check if O has won
                //check rows
                for(let i = 0; i < 3; i++){
                    let line = true;
                    for(let j = 0; j < 3; j++){
                      if (document.getElementById(i.toString()+j.toString()).innerHTML == 'X'){
                        line = false;
                      }
                    }
                    if(line == true)
                        return "O has won";
                }

                //check columns
                for(let i = 0; i< 3; i++){
                    let line = true;
                    for(let j = 0; j < 3; j ++){
                         if (document.getElementById(j.toString()+i.toString()).innerHTML == 'X'){
                        line = false;
                      }
                    }
                    if(line == true)
                        return "O has won";
                }

                //check diagonals
                line = true;
                for(let i = 0; i<3; i++){
                    if (document.getElementById(i.toString()+i.toString()).innerHTML == 'X'){
                        line = false;
                      }
                }
                if(line == true)
                    return "O has won";

               line = true;
               if(document.getElementById("02").innerHTML == 'X'){
                line = false;
               }
               if(document.getElementById("11").innerHTML == 'X'){
                line = false;
               }
               if(document.getElementById("20").innerHTML == 'X'){
                line = false;
               }
               if(line == true){
                    return "O has won";
               }

               //game still in progress
               return "Game is not finished";


            }
        });
    </script>
</body>

</html>
