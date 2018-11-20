var winMgr = {
  windowContainerSimple: null, // the simple name of the container that holds all windows
  windowContainer: null, // the container object (used for sizing, emptying, etc)
  containerWidth: null, // the width of the container divided in half to square out 4 windows
  containerHeight: null, // the height of the container divided in half to square out 4 windows
  window: null, // the window management system, itself (ORIGINALLY wins/myWins)
  winPos: {}, // the positions of the windows
  wins: {}, // the windows that are created
  winPosOffset: {'x': 0, 'y': 0}, // offset for window position automatic creation

  init: function(simpleContainer) {
    winMgr.windowContainerSimple = simpleContainer; // the simple name of the window container, without identifying the object
    winMgr.windowContainer = $("#" + this.windowContainerSimple); // the window container object
    winMgr.containerWidth = this.windowContainer.outerWidth() / 2; // width divided in half for initial view of windows
    winMgr.containerHeight = this.windowContainer.outerHeight() / 2; // height divided in half for initial view of windows
    winMgr.window = new dhtmlXWindows(); // the window management system itself

    winMgr.window.attachEvent("onClose", function(win) {
      let eleID = win._idd;

      $(".widget-item[data-window='" + eleID + "']").removeClass("widget-active");

      winMgr.winPosOffset.x -= 20;
      winMgr.winPosOffset.y -= 20;

      return winMgr.removeWinVar(eleID);
    });

    winMgr.autoTitle(); // sets the title above navigation, next to widgets, automatically
  },
  newWin: function(winVarName, pos, windowTitle, attachObj, ajaxURL, ajaxPass) {
    /***************************************************************************
     * Manages the spawning of new windows
     * *********************************************************************** *
     * @winVarName = name of container that holds the windows
     * @pos = which position to spawn in
     * @windowTitle = the title of the window itself
     * @attachObj = if attaching an in-page HTML object, deprecated
     * @ajaxURL = the URL of the window that we're loading via AJAX
     * @ajaxPass = any POST variables to send to the window alongside the AJAX request
    **************************************************************************/
    winMgr.winPos[winVarName] = {}; // initialize the empty container for window positions

    // based on the numerical position of the window
    // 1 = top left; 2 = top right; 3 = bottom left; 4 = bottom right
    switch(pos) {
      case 1:
        winMgr.winPos[winVarName].x = 0; // set the object of {window position.window name.x} to 0
        winMgr.winPos[winVarName].y = 0; // set the object of {window position.window name.y} to 0
        break;
      case 2:
        winMgr.winPos[winVarName].x = winMgr.containerWidth;
        winMgr.winPos[winVarName].y = 0;
        break;
      case 3:
        winMgr.winPos[winVarName].x = 0;
        winMgr.winPos[winVarName].y = winMgr.containerHeight;
        break;
      case 4:
        winMgr.winPos[winVarName].x = winMgr.containerWidth;
        winMgr.winPos[winVarName].y = winMgr.containerHeight;
        break;
      default:
        winMgr.winPos[winVarName].x = (winMgr.containerWidth / 2) + winMgr.winPosOffset.x;
        winMgr.winPos[winVarName].y = (winMgr.containerHeight / 2) + winMgr.winPosOffset.y;

        winMgr.winPosOffset.x += 20;
        winMgr.winPosOffset.y += 20;
        break;
    }

    // create a new window based on {this object.wins.window variable name}
    winMgr.wins[winVarName] = winMgr.window.createWindow({
      id: winVarName, // this windows variable name (crm, quotes, active_ops, etc)
      left: winMgr.winPos[winVarName].x, // the position of that window
      top: winMgr.winPos[winVarName].y, // the position of that window
      width: winMgr.containerWidth, // width of the containers (generic)
      height: winMgr.containerHeight, // height of the containers (generic)
      caption: windowTitle // the title of the window, passed through the function
    });

    if(attachObj !== null) { // if we're attaching an object
      winMgr.wins[winVarName].attachObject(attachObj);
    } else if(ajaxURL !== null) { // otherwise, if we're attaching ajax
      winMgr.wins[winVarName].attachURL(ajaxURL, true, ajaxPass);
    }

    winMgr.window.attachViewportTo(winMgr.windowContainerSimple); // stick the windows inside of the container that we're spawning them in

    winMgr.window.window(winVarName).addUserButton('popout', 0, 'Pop Out'); // add the pop-out button
    winMgr.window.window(winVarName).keepInViewport(true); // keep the windows in that viewport (do not allow draginging outside of that box)

    winMgr.window.window(winVarName).button('popout').attachEvent("onClick", function() {
      // open a new window (forced) on user, maximized with the window requested
      window.open('/main.php?page=crm/index&maximized=true&win=' + winVarName, '_blank', 'toolbar=0,location=0,menubar=0,left=0,top=0');
      winMgr.closeWin(winVarName); // close the widget that was popped out

      return false;
    });
  },
  newAutoWin: function(winVarName, maximized) {
    let windowTranslator = {
      'crm': {
        'position': 1, // first 4 definitions are defaulted to this position
        'windowTitle': 'CRM',
        'attachObj': null,
        'ajaxURL': '/html/windows/crm.php'
      },
      'quotes': {
        'position': 2,
        'windowTitle': 'Quotes',
        'attachObj': null,
        'ajaxURL': '/html/windows/quotes.php'
      },
      'production': {
        'position': 3,
        'windowTitle': 'Production',
        'attachObj': null,
        'ajaxURL': '/html/windows/production.php'
      },
      'operations': {
        'position': 4,
        'windowTitle': 'Active Operations',
        'attachObj': null,
        'ajaxURL': '/html/windows/active_operations.php'
      },
      'calendar': {
        // position is skipped
        'windowTitle': 'Calendar',
        'attachObj': null,
        'ajaxURL': '/html/calendar/index.php'
      },
      'email': {
        // position skipped
        'windowTitle': 'Email',
        'attachObj': null,
        'ajaxURL': '/html/mail/cross_page.php'
      },
      'opl': {
        // position skipped
        'windowTitle': 'OPL',
        'attachObj': null,
        'ajaxURL': '/html/opl/index.php'
      },
      'reports': {
        // position skipped
        'windowTitle': 'Reports',
        'attachObj': null,
        'ajaxURL': '/html/sales_list.php?win=true'
      },
      'inventory': {
        // position skipped
        'windowTitle': 'Inventory',
        'attachObj': null,
        'ajaxURL': '/html/inventory/index.php'
      },
      'accounting': {
        // position skipped
        'windowTitle': 'Accounting',
        'attachObj': null,
        'ajaxURL': '/html/accounting/index.php'
      },
      'feedback': {
        // position skipped
        'windowTitle': 'Feedback/Tasks',
        'attachObj': null,
        'ajaxURL': '/html/tasks.php'
      },
      'dealers': {
        // position skipped
        'windowTitle': 'Dealer Management',
        'attachObj': null,
        'ajaxURL': '/html/windows/dealers.php'
      },
      'database': {
        // position skipped
        'windowTitle': 'Database Management',
        'attachObj': null,
        'ajaxURL': '/html/windows/database_mgmt.php'
      }
    };

    if(windowTranslator[winVarName] !== undefined) {
      winMgr.newWin(winVarName, windowTranslator[winVarName].position, windowTranslator[winVarName].windowTitle, windowTranslator[winVarName].attachObj, windowTranslator[winVarName].ajaxURL);

      if(winMgr.winPosOffset.x > 0 && winMgr.winPosOffset.y > 0) {
        winMgr.winPosOffset.x += 20;
        winMgr.winPosOffset.y += 20;
      }

      if(maximized) {
        winMgr.window.window(winVarName).maximize();
      }

      return true;
    } else {
      console.log("Error: Window Translator was unable to find the information for window " + winVarName);

      return false;
    }
  },
  getWins: function() {
    return winMgr.window;
  },
  removeWinVar: function(w) {
    return delete winMgr.wins[w];
  },
  setFocus: function(focusContainerName) {
    winMgr.window.window(focusContainerName).bringToTop();
    $(".request_header").text(winMgr.window.window(focusContainerName).getText());
  },
  autoTitle: function() {
    winMgr.getWins().attachEvent("onFocus", function(win) {
      $(".request_header").text(win.getText());
    });
  },
  closeWin: function(winName) {
    winMgr.window.window(winName).close();
  }
};