<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Quick start with dhtmlxGrid</title>
  <link rel="stylesheet" type="text/css" href="/assets/plugins/dhtmlxGrid/dhtmlxgrid.css">
  <script src="/assets/plugins/dhtmlxGrid/dhtmlxgrid.js"></script>
</head>
<body>
<div id="gridbox" style="width:300px;height:400px;"></div>
<script>
  mygrid = new dhtmlXGridObject('gridbox');

  //the path to images required by grid
  mygrid.setImagePath("./codebase/imgs/");
  mygrid.setHeader("Sales,Book title,Author,Price");//the headers of columns
  mygrid.setInitWidths("100,250,150,100");          //the widths of columns
  mygrid.setColAlign("right,left,left,left");       //the alignment of columns
  mygrid.setColTypes("ro,ed,ed,ed");                //the types of columns
  mygrid.setColSorting("int,str,str,int");          //the sorting types
  mygrid.init();      //finishes initialization and renders the grid on the page

  data={
    rows:[
      { id:1, data: ["A Time to Kill", "John Grisham", "100"]},
      { id:2, data: ["Blood and Smoke", "Stephen King", "1000"]},
      { id:3, data: ["The Rainmaker", "John Grisham", "-200"]}
    ]
  };

  mygrid.parse(data,"json"); //takes the name and format of the data source
</script>
</body>
</html>