// test-db.js
const mysql = require('mysql2');

// Create the connection (adjust credentials if needed)
const dbCon = mysql.createConnection({
  host: 'localhost',
  user: '436_mysql_user',
  password: '123pwd456',
  database: '436db'
});

// Try to connect
dbCon.connect(error => {
  if (error) {
    console.log('Error connecting to DB:', error);
    return;
  }
  console.log('DB Connection established');

  // Check which tables exist
  dbCon.query("SHOW TABLES;", (err, results) => {
    if (err) {
      console.log('Query error:', err);
    } else {
      console.log('Tables in database:');
      console.table(results);
    }
    dbCon.end(); // close the connection when done
  });
});
