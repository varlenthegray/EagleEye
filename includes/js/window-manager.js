var winMgr = {
  windowContainerSimple: null, // the simple name of the container that holds all windows
  windowContainer: null, // the container object (used for sizing, emptying, etc)
  containerWidth: null, // the width of the container divided in half to square out 4 windows
  containerHeight: null, // the height of the container divided in half to square out 4 windows
  window: null, // the window management system, itself (ORIGINALLY WINS)
  winPos: {}, // the positions of the windows
  wins: {}, // the windows that are created

  init: function() {
    winMgr.windowContainerSimple = 'crmUID';
    winMgr.windowContainer = $("#" + this.windowContainerSimple);
    winMgr.containerWidth = this.windowContainer.outerWidth() / 2;
    winMgr.containerHeight = this.windowContainer.outerHeight() / 2;
    winMgr.window = new dhtmlXWindows();
  },

  // @winVarName = name of container that holds the windows, @pos = which position to spawn in
  newWin: function(winVarName, pos, windowTitle, attachObj) {
    winMgr.winPos[winVarName] = {};

    switch(pos) {
      case 1:
        winMgr.winPos[winVarName].x = 0;
        winMgr.winPos[winVarName].y = 0;
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

        break;
    }

    winMgr.wins[winVarName] = winMgr.window.createWindow({
      id: winVarName,
      left: winMgr.winPos[winVarName].x,
      top: winMgr.winPos[winVarName].y,
      width: winMgr.containerWidth,
      height: winMgr.containerHeight,
      caption: windowTitle
    });

    winMgr.wins[winVarName].attachObject(attachObj);

    winMgr.window.attachViewportTo(winMgr.windowContainerSimple);

    winMgr.window.window(winVarName).addUserButton('popout', 0, 'Pop Out');
    winMgr.window.window(winVarName).keepInViewport(true);
  },

  getWins: function() {
    return winMgr.window;
  },

  setFocus: function(focusContainerName) {
    winMgr.window.window(focusContainerName).bringToTop();
    $(".request_header").text(winMgr.window.window(focusContainerName).getText());
  }
};