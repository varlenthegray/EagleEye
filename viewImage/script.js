/*global document*/
var formDataToUpload;
var contentType = 'image/png';
var b64Data = '';

$(document).ready(function() {
  $("#uploadimage").on('submit', function(e) {
    e.preventDefault();
    $("#message").empty();
    $('#loading').show();
    $.ajax({
      url: "/viewImage/ajax_php_file.php", // Url to which the request is send
      type: "POST", // Type of request to be send, called as method
      data: formDataToUpload, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
      contentType: false, // The content type used when sending data to the server.
      cache: false, // To unable request pages to be cached
      processData: false, // To send DOMDocument or non processed data file it is set to false
      success: function(data) // A function to be called if request succeeds
      {
        console.log(data);
        $('#loading').hide();
        $("#message").html(data);
      }
    });
  });
});

(function($) {
  var defaults;
  $.event.fix = (function(originalFix) {
    return function(event) {
      event = originalFix.apply(this, arguments);
      if (event.type.indexOf("copy") === 0 || event.type.indexOf("paste") === 0) {
        event.clipboardData = event.originalEvent.clipboardData;
      }
      return event;
    };
  })($.event.fix);
  defaults = {
    callback: $.noop,
    matchType: /image.*/
  };
  return ($.fn.pasteImageReader = function(options) {
    if (typeof options === "function") {
      options = {
        callback: options
      };
    }
    options = $.extend({}, defaults, options);
    return this.each(function() {
      var $this, element;
      element = this;
      $this = $(this);
      return $this.bind("paste", function(event) {
        var clipboardData, found;
        found = false;
        clipboardData = event.clipboardData;
        return Array.prototype.forEach.call(clipboardData.types, function(type, i) {
          var file, reader;
          if (found) {
            return;
          }
          if (
            type.match(options.matchType) ||
            clipboardData.items[i].type.match(options.matchType)
          ) {
            file = clipboardData.items[i].getAsFile();
            reader = new FileReader();
            reader.onload = function(evt) {
              return options.callback.call(element, {
                dataURL: evt.target.result,
                event: evt,
                file: file,
                name: file.name
              });
            };
            reader.readAsDataURL(file);
            return (found = true);
          }
        });
      });
    });
  });
})(jQuery);

function b64toBlob(b64Data, contentType, sliceSize) {
  contentType = contentType || '';
  sliceSize = sliceSize || 512;

  var byteCharacters = atob(b64Data);
  var byteArrays = [];

  for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    var slice = byteCharacters.slice(offset, offset + sliceSize);

    var byteNumbers = new Array(slice.length);
    for (var i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    var byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
  }

  var blob = new Blob(byteArrays, {type: contentType});
  return blob;
}

var dataURL, filename;
var $data, $size, $type, $width, $height;


$("html").pasteImageReader(function(results) {
  console.log(results);
  filename = results.filename, dataURL = results.dataURL;

  let t1 = dataURL.split(",");
  b64Data = t1[1];

  var blob = b64toBlob(b64Data, contentType);
  var blobUrl = URL.createObjectURL(blob);

  console.log(b64Data);
  $data.text(dataURL);
  $size.val(results.file.size);
  $type.val(results.file.type);
  var img = document.createElement("img");
  img.src = blobUrl;
  var w = img.width;
  var h = img.height;
  $width.val(w);
  $height.val(h);
  formDataToUpload = new FormData();
  formDataToUpload.append("image", blob, "image.png");
  return $(".active")
    .css({
      backgroundImage: "url(" + dataURL + ")"
    })
    .data({
      width: w,
      height: h
    });
});

$(function() {
  $data = $(".data");
  $size = $(".size");
  $type = $(".type");
  $width = $("#width");
  $height = $("#height");
  $(".target").on("click", function() {
    var $this = $(this);
    var bi = $this.css("background-image");
    if (bi !== "none") {
      $data.text(bi.substr(4, bi.length - 6));
    }

    $(".active").removeClass("active");
    $this.addClass("active");

    $this.toggleClass("contain");

    $width.val($this.data("width"));
    $height.val($this.data("height"));
    if ($this.hasClass("contain")) {
      $this.css({
        width: $this.data("width"),
        height: $this.data("height"),
        "z-index": "10"
      });
    } else {
      $this.css({
        width: "",
        height: "",
        "z-index": ""
      });
    }
  });
});