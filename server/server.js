/******************************************
 * Includes/requires
 *****************************************/
var https = require('https');
var fs = require('fs');
var sconn = require('socket.io');
var mysql = require('mysql');

var scriptArgs = process.argv; // grab the arguments from the server

var sqlLoc; // SQL database connection object
var server; // server connection object
var port; // port for the server

// if we're receiving the console command that we're launching dev environment
if(scriptArgs[2] === 'dev') {
  // set the SQL data for dev
  sqlLoc = {host:'dev.3erp.us',user:'dev_remote.dev',password:'o4J4G91@uvw%&ptkMOwZ',database:'3erp_dev'};

  // setup the dev server for HTTPS
  server = https.createServer({
    key: fs.readFileSync('/home/threeerp/domains/dev.3erp.us/ssl.key'),
    cert: fs.readFileSync('/home/threeerp/domains/dev.3erp.us/ssl.cert'),
    ca: fs.readFileSync('/home/threeerp/domains/dev.3erp.us/ssl.ca'),
    requestCert: false,
    rejectUnauthorized: false
  }, sconn);

  // port 4k is the dev server
  port = 4000;
} else {
  // set the SQL data for live
  sqlLoc = {host:'3erp.us',user:'remote.3erp',password:'Gl^1k6LyteGpAJ0SoOcU',database:'3erp'};

  // setup the live server for HTTPS
  server = https.createServer({
    key: fs.readFileSync('/home/threeerp/ssl.key'),
    cert: fs.readFileSync('/home/threeerp/ssl.cert'),
    ca: fs.readFileSync('/home/threeerp/ssl.ca'),
    requestCert: false,
    rejectUnauthorized: false
  }, sconn);

  // port 4100 is the live server
  port = 4100;
}

// connect to the database using the specific SQL details
var db = mysql.createConnection(sqlLoc);

/******************************************
 * Global connection
 *****************************************/
server.listen(port); // listen to the server
var socket = require('socket.io').listen(server); // setup socketio connection

/******************************************
 * End global connection
 * ----------------------------------------
 * Begin global variable declaration
 *****************************************/
var conn = {}; // storage for everything, expanding and contracting as needed

/******************************************
 * End global variable declaration
 * ----------------------------------------
 * Begin function declaration
 *****************************************/
// Error handling
function reportErr(client, e) {
  socket.to(client.id).emit("err", e);
}
// End error handling

/******************************************
 * End function declaration
 * ----------------------------------------
 * Begin socket handling
 *****************************************/
socket.on('connect', function (client) {
  // Create an empty object DIRECTLY related to the client.id (socket connection for that individual) called user object
  /** @var object - provides a location for storage directly related to that client, also allows cleanup */
  if(conn[client.id] === undefined) {
    conn[client.id] = {};
  }

  // destruction of the socket connection
  client.on("disconnect", function() {
    try {
      delete conn[client.id]; // delete the client's information from the connection pool
    } catch(e) {
      console.log("Disconnect error: " + e); // log an error
    }
  });

  // updating and fetching user information, uk allows us to safely identify the user without someone being able to change to another user
  /** @var string - uk - unique user key that identifies that user in the database */
  client.on("updateUK", function(uk) {
    try {
      // update the user object for the unique key
      conn[client.id].uk = uk;

      // grab the database information
      db.query("SELECT * FROM user WHERE unique_key = ?", [conn[client.id].uk], function(err, r) {
        conn[client.id].userInfo = r;

        if(err) {
          // report the error to the console, ignore the results of e for now - output to server console if error occurs
          reportErr(client, "Failed to obtain user profile from server. Please report this error to IT.");
        }
      });
    } catch(e) {
      console.log("updateUK Error: " + e); // log an error
    }
  });

  // updating the operation queue, this updates it for everyone globally
  client.on("updateQueue", function() {
    try {
      socket.emit("catchQueueUpdate"); // tell the pages to pull AJAX data, tons of information so no need to fetch via database and inject results
    } catch(e) {
      console.log("updateQueue Error: " + e); // log an error
    }
  });

  // updates the SO queue, updating it for everyone globally
  client.on("updateSO", function() {
    try {
      socket.emit("catchSOUpdate"); // tell the page to pull AJAX data, tons of information so no need to fetch via database and inject results
    } catch(e) {
      console.log("updateSO Error: " + e); // log an error
    }
  });

  client.on("refreshAll", function() {
    try {
      socket.emit("catchRefresh"); // send a refresh to every connection
    } catch(e) {
      console.log("refreshAll Error: " + e); // log an error
    }
  });

  // reception that someone is editing the OPL
  client.on("oplEditing", function(data) {
    try {
      console.log(data);

      socket.emit("oplEditLock", data);
    } catch(e) {
      console.log("oplEditing Error: " + e);
    }
  });
});

console.log("3erp active and listening on Port " + port + "."); // let the console know what we're doing