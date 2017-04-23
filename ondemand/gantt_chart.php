<?php
header('Content-Type: application/json');
?>
[{
"category": "Sample",
    "segments": [{
        "start": 420,
        "duration": 120,
        "color": "rgba(105, 255, 105, 0.8)",
        "task": "165-Sample Approved"
    },{
        "duration": 420,
        "color": "rgba(105, 255, 105, 0.8)",
        "task": "175-Production"
    },{
        "duration": 60,
        "color": "rgba(255, 253, 105, 0.8)",
        "start": 3600,
        "task": "180-Customer Delivery"
    }]
},{
"category": "Main",
    "segments": [{
        "start": 600,
        "duration": 120,
        "color": "rgba(105, 255, 105, 0.8)",
        "task": "520-Bore, Dado, Pocket Hole"
    },{
        "duration": 1110,
        "color": "rgba(105, 255, 105, 0.8)",
        "task": "540-Finishing"
    },{
        "start": 1980,
        "duration": 240,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "560-Assembly"
    }]
},{
    "category": "Door/Drawer",
    "segments": [{
        "start": 720,
        "duration": 30,
        "color": "rgba(255, 253, 105, 0.8)",
        "task": "430-Door Pick Up"
    },{
        "start": 870,
        "duration": 120,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "440-Door Finishing"
    },{
        "start": 1830,
        "duration": 30,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "445-Door Inspection"
    }]
},{
    "category": "Customs",
    "segments": [{
        "start": 420,
        "duration": 300,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "610-Custom"
    },{
        "duration": 30,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "615-Custom Inspection"
    },{
        "duration": 60,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "620-Custom Rework"
    },{
        "duration": 30,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "630-Finishing"
    },{
        "duration": 30,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "635-Finishing Inspection"
    },{
        "duration": 120,
        "color": "rgba(255, 194, 105, 0.8)",
        "task": "640-Finishing Rework"
    }]
}
]
