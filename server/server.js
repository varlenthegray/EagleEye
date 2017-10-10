var io = require('socket.io');
var mysql = require('mysql');
var request = require('request');
var socket = io.listen(3100);
var currentConnections = {};

var db = mysql.createConnection({
    host:'dev.3erp.us',user:'dev_remote.dev',password:'o4J4G91@uvw%&ptkMOwZ',database:'3erp_dev'
    //host:'3erp.us',user:'remote.3erp',password:'Gl^1k6LyteGpAJ0SoOcU',database:'3erp'
});

function queryDB(q, v) {
    "use strict";

    var query = null;

    if(v !== undefined) {
        query = db.query(q, v);
    } else {
        query = db.query(q);
    }

    query.on('error', function(e) {
        var errq = db.query("INSERT INTO log_error ?", [e]);

        errq.on('error', function(errqerr) {
            console.log("Unable to insert error into logs, error: " + errqerr);
        });
    }).on('result', function(r) {
        return r;
    });
}

socket.on('connection', function (socket) {
    "use strict";

    console.log("New user connection.");
    socket.emit("news", {hello: 'world'});
});

console.log("3erp active and listening on Port 3100."); // let the console know what we're doing