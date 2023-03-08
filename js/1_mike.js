// VanillaTilt.init(document.querySelectorAll('.card'), {
//     reset: false,
//     max : 20
// });

/**
 *
 *  FORM SERIALIZATION (GOOGLE CODE) // EDITED input color/date/search
 *
 */
function serialize(form) {
  if (!form || form.nodeName !== "FORM") {
    return;
  }
  var i,
    j,
    q = [];
  for (i = form.elements.length - 1; i >= 0; i = i - 1) {
    if (form.elements[i].name === "") {
      continue;
    }
    switch (form.elements[i].nodeName) {
      case "INPUT":
        switch (form.elements[i].type) {
          case "text":
          case "email":
          case "search":
          case "hidden":
          case "password":
          case "button":
          case "reset":
          case "color":
          case "date":
          case "datetime-local":
          case "submit":
            q.push(
              form.elements[i].name +
                "=" +
                encodeURIComponent(form.elements[i].value)
            );
            break;
          case "checkbox":
          case "radio":
            if (form.elements[i].checked) {
              q.push(
                form.elements[i].name +
                  "=" +
                  encodeURIComponent(form.elements[i].value)
              );
            }
            break;
          case "file":
            break;
        }
        break;
      case "TEXTAREA":
        q.push(
          form.elements[i].name +
            "=" +
            encodeURIComponent(form.elements[i].value)
        );
        break;
      case "SELECT":
        switch (form.elements[i].type) {
          case "select-one":
            q.push(
              form.elements[i].name +
                "=" +
                encodeURIComponent(form.elements[i].value)
            );
            break;
          case "select-multiple":
            for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
              if (form.elements[i].options[j].selected) {
                q.push(
                  form.elements[i].name +
                    "=" +
                    encodeURIComponent(form.elements[i].options[j].value)
                );
              }
            }
            break;
        }
        break;
      case "BUTTON":
        switch (form.elements[i].type) {
          case "reset":
          case "submit":
          case "button":
            q.push(
              form.elements[i].name +
                "=" +
                encodeURIComponent(form.elements[i].value)
            );
            break;
        }
        break;
    }
  }
  return q.join("&");
}

//Tween Function
function easeOut(progress, power = 2) {
  // return 1 - (1 - progress) ** power;
  return 1 - Math.pow(1 - progress, power);
}
function easeInOutCirc(x) {
  return x < 0.5
    ? (1 - Math.sqrt(1 - Math.pow(2 * x, 2))) / 2
    : (Math.sqrt(1 - Math.pow(-2 * x + 2, 2)) + 1) / 2;
}
function easeInOutBack(x) {
  var c1 = 1.70158;
  var c2 = c1 * 1.525;
  return x < 0.5
    ? (Math.pow(2 * x, 2) * ((c2 + 1) * 2 * x - c2)) / 2
    : (Math.pow(2 * x - 2, 2) * ((c2 + 1) * (x * 2 - 2) + c2) + 2) / 2;
}
function easeInBack(x) {
  var c1 = 1.70158;
  var c3 = c1 + 1;
  return c3 * x * x * x - c1 * x * x;
}

function tween({
  from = 0,
  to = 1,
  duration = 300,
  ease = easeOut,
  onUpdate,
  onComplete,
} = {}) {
  const delta = to - from;
  const startTime = performance.now();
  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const latest = from + ease(progress) * delta;
    if (onUpdate) {
      onUpdate(latest);
    }
    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      if (onComplete) {
        onComplete();
      }
    }
  }
  requestAnimationFrame(update);
}
function getElementY(query, selector = false, bottom = false) {
  var ele = selector ? query : document.querySelector(query);
  return (
    window.pageYOffset +
    (bottom
      ? ele.getBoundingClientRect().bottom
      : ele.getBoundingClientRect().top)
  );
}
function debounce(func, wait, immediate) {
  var timeout;
  return function () {
    var context = this,
      args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      timeout = null;
      if (!immediate) func.apply(context, args);
    }, wait);
    if (immediate && !timeout) func.apply(context, args);
  };
}

/*************************************
 *
 *   TRACKING HELPERS FUNCTIONS
 *
 **************************************/
//Get Elements Position in array (debounced, in Resize event)
function trackPos(elements, output) {
  forEach(elements, function (index, value) {
    output[index] = {
      top: getElementY(elements[index], true),
      bot: getElementY(elements[index], true, true),
    };
  });
}
//Write CSS in ROOT based on element's visibility on screen, using precalculated Elements Position (trackPos)
//0 to 1 switch OFF/ON
//0 to 1 progressive decimals

//NOW ! We can make funny things with CSS now without any repaint/reflow/browser lag
function markPos(trackPos, playstate = [], switchratio, output) {
  var trackers = Object.entries(trackPos);
  forEach(trackers, function (index, value) {
    // var q = Math.round(1.165/(value[1].bot) * latestKnownScrollY * 100) / 100;
    // var q = Math.round(0.55/(value[1].bot) * latestKnownScrollY * 100) / 100;

    var q = Math.round((1 / value[1].bot) * latestKnownScrollY * 100) / 100;

    rootCSS("--s" + index, q >= 0.5 ? 1 : 0); //Switcher 0   -> 1 -- Net
    rootCSS("--t" + index, q >= 1 ? 1 : q); //Target   0.1 -> 1 -- Progressif

    if (playstate.includes(index)) {
      // rootCSS("--s"+index+"ps",   (q >= switchratio ? "paused" : "playing"));    //css animation-play-state
      rootCSS("--s" + index + "ps", q >= switchratio ? "playing" : "paused"); //css animation-play-state
    }
    //console.log(compare);
  });
}

/*************************************
 *
 *   HELPERS
 *
 **************************************/

//Window Size
var w =
  window.innerWidth ||
  document.documentElement.clientWidth ||
  document.body.clientWidth;
var h =
  window.innerHeight ||
  document.documentElement.clientHeight ||
  document.body.clientHeight;

function getRandomInt(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

//Extract php query urls
function queryString(url) {
  var params = [];
  var parser = document.createElement("a");
  parser.href = url;
  var query = parser.search.substring(1);
  var vars = query.split("&");
  for (var i = 0; i < vars.length; i++) {
    var pair = vars[i].split("=");
    params[pair[0]] = decodeURIComponent(pair[1]);
  }
  return params;
}

//EZ parse HTML from string
function createElementFromHTML(htmlString) {
  var div = document.createElement("div");
  div.innerHTML = htmlString.trim();
  return div;
}
//Closest 'b' element 'a'
// if (window.Element && !Element.prototype.closest) {
//     Element.prototype.closest =
//         function (s) {
//             var matches = (this.document || this.ownerDocument).querySelectorAll(s),
//                 i,
//                 el = this;
//             do {
//                 i = matches.length;
//                 while (--i >= 0 && matches.item(i) !== el) { };
//             } while ((i < 0) && (el = el.parentElement));
//             return el;
//         };
// }
if (!Element.prototype.matches)
  Element.prototype.matches =
    Element.prototype.msMatchesSelector ||
    Element.prototype.webkitMatchesSelector;

if (!Element.prototype.closest)
  Element.prototype.closest = function (s) {
    var el = this;
    if (!document.documentElement.contains(el)) return null;
    do {
      if (el.matches(s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType == 1);
    return null;
  };

if (!Element.prototype.addEventListeners) {
  Element.prototype.addEventListeners = function (events, fn) {
    events.split(",").forEach(function (event) {
      this.addEventListener(event, fn, false);
    });
  };
}

//Does this element exist in the DOM
function doesExist(el) {
  var element = document.querySelector(el);
  return typeof element != "undefined" && element != null;
}

//Does Vartiable Exist, is not empty or null
function doesVarExist(element) {
  return typeof element != "undefined" && element != null && element != "";
}

//Useless functions to make it a little blurry when compiled / minified
function hasAttr(el, attr) {
  return el.hasAttribute(attr);
}
function getAttr(el, attr) {
  return el.getAttribute(attr);
}

//ForEach bypass
function forEach(array, callback, scope) {
  for (var i = 0; i < array.length; i++) {
    callback.call(scope, i, array[i]); // passes back stuff we need
  }
}
//Is Element child of certain element
function isChild(obj, parentObj) {
  while (
    obj != undefined &&
    obj != null &&
    obj.tagName.toUpperCase() != "BODY"
  ) {
    if (obj == parentObj) {
      return true;
    }
    obj = obj.parentNode;
  }
  return false;
}
//Get siblings elements
let getSiblings = function (e) {
  let siblings = [];
  if (!e.parentNode) {
    return siblings;
  }
  let sibling = e.parentNode.firstChild;
  while (sibling) {
    if (sibling.nodeType === 1 && sibling !== e) {
      siblings.push(sibling);
    }
    sibling = sibling.nextSibling;
  }
  return siblings;
};

function search(e) {
  console.log(e.target);
  var input = e.target;
  var target = document.querySelector(input.getAttribute("data-search"));
  var results = target.querySelectorAll("[data-result]");
  var items = results;
  var text = e.target.value.trim();
  var pat = new RegExp(text, "i");

  forEach(items, function (i, item) {
    console.log(item);

    if (pat.test(item.innerText.trim())) {
      item.classList.remove("d-none");
      item.removeAttribute("style");
    } else {
      item.classList.add("d-none");
      // item.style.display = 'none !important'; //Does NOT work
      item.setAttribute("style", "display:none !important");
    }
  });
}
//IF RADIO/CHECKBOX should trigger submit on change
function radioCheckboxSwapper(e) {
  if (hasAttr(e.target, "data-submit")) {
    var closestform = e.target.closest("form");
    closestform.querySelector('[type="submit"]').click();
    e.preventDefault;
  }
}
function onSubmit(e) {
  postAndReplace(e);
}

function onClick(e) {
  postAndReplace(e);

  if (e.target.classList.contains("generate")) {
    var password = generate();
    var target = document.querySelector(e.target.dataset.target);
    target.value = password;
    e.preventDefault;
  }
}

function onChange(e) {
  //   if (e.target.getAttribute("type") == "search") {
  //     search(e);
  //   }
  radioCheckboxSwapper(e);
  // if (e.target.classList.contains("filterbox")){
  // e.target.closest('.filter').parentElement.querySelector('[type=submit]').click()
  // console.log()
  // }
  //   if (e.target.id == "upload-image") {
  //     $temp = e.target;
  //     var file = e.target.files[0],
  //       imageType = /image.*/;

  //     if (!file.type.match(imageType)) return;
  //     var reader = new FileReader(e);
  //     reader.onload = fileOnload;
  //     reader.readAsDataURL(file);
  //   }

  // console.log(e.target);
}

function onInput(e) {
  if (e.target.type == "color" && e.target.name == "green") {
    mixTheme(e.target.value);
    // console.log(mixTheme(e.target.value));
  }
  if (e.target.getAttribute("type") == "search") {
    search(e);
  }
}

//Write CSS in ROOT
function rootCSS(property, value, force = true) {
  //Single
  //We rewrite only interesting stuffs
  if (force) {
    document.documentElement.style.setProperty(property, value);
    //console.log("forced", property, value);
  } else {
    //I...tried getComputedStyle but it's too fucken slow
    if (compare[property] != value) {
      compare[property] = value;
      document.documentElement.style.setProperty(property, value);
      //console.log("new", property, value);
    } else {
      //console.log("skipped", property, value);
    }
  }
}

function onSwipe(e) {
  const side = document.querySelector("#side");
  const togg = document.querySelector("#toggleSide");
  if (e.target == side || isChild(e.target, side)) {
    if (e.type == "swiperight") {
      togg.checked = true;
    }
    if (e.type == "swipeleft") {
      togg.checked = false;
    }
  }
}
// var range = document.querySelector('#range');

// window.addEventListener('scroll', onScroll);

document.addEventListener("change", onChange);
document.addEventListener("click", onClick);
document.addEventListener("input", onInput);
document.addEventListener("submit", onSubmit);
document.addEventListener("swiperight", onSwipe);
document.addEventListener("swipeleft", onSwipe);
// window.addEventListener('resize', debounce(onResize, 50), false);
// document.addEventListener("DOMContentLoaded", function (e) {
//     onScroll();
//     onResize();
// });

// let details = document.querySelectorAll('details');
// details.forEach(function (detail) {
//     detail.addEventListener('click', function (e) {
//         getSiblings(detail).forEach(function (sibling) {

//             detail.toggleAttribute('open');
//             sibling.open = false;
//         });
//     });
// });

function fileOnload(e) {
  var $img = document.createElement("img");
  $img.setAttribute("src", e.target.result);
  $img.setAttribute("id", $temp);
  $img.addEventListener("load", function (i) {
    var srcCanvas = document.createElement("canvas"),
      dstCanvas = document.createElement("canvas"); //Resize
    var srcContext = srcCanvas.getContext("2d"),
      dstContext = dstCanvas.getContext("2d");
    srcContext.imageSmoothingEnabled = true;
    dstContext.imageSmoothingEnabled = true;

    //Generate Noise
    var nseCanvas = document.createElement("canvas"),
      nseContext = nseCanvas.getContext("2d"),
      x,
      y,
      number,
      opacity = 0.3;
    nseCanvas.width = 80;
    nseCanvas.height = 80;
    nseContext.fillStyle = "#808080"; //Neutral Gray
    nseContext.fillRect(0, 0, 80, 80);
    for (x = 0; x < nseCanvas.width; x++) {
      for (y = 0; y < nseCanvas.height; y++) {
        number = Math.floor(Math.random() * 60);
        nseContext.fillStyle =
          "rgba(" + number + "," + number + "," + number + "," + opacity + ")";
        nseContext.fillRect(x, y, 1, 1);
      }
    }
    //Resize and Crop
    var $w = 1280;
    var $h = 720;
    $portraitToLandscape =
      i.target.height < i.target.width
        ? i.target.height / i.target.width
        : i.target.width / i.target.height;
    srcCanvas.width = $w;
    //        srcCanvas.height = srcCanvas.width * (i.target.height / i.target.width); //Ratio
    srcCanvas.height = srcCanvas.width * $portraitToLandscape;

    if (i.target.height > i.target.width) {
      //Save Context State "unrotated"
      srcContext.save();
      //Rotate
      srcContext.translate(srcCanvas.width / 2, srcCanvas.height / 2);
      srcContext.rotate((90 * Math.PI) / 180);
      srcContext.drawImage(
        this,
        -$h / 2,
        -$w / 2,
        srcCanvas.height,
        srcCanvas.width
      ); //Draw proportionnally
      //Restore state "unrotated"
      srcContext.restore();
    } else {
      srcContext.drawImage(this, 0, 0, srcCanvas.width, srcCanvas.height); //Draw proportionnally
    }
    dstCanvas.width = $w;
    dstCanvas.height = $h;
    dstContext.drawImage(
      srcCanvas,
      srcCanvas.width / 2 - dstCanvas.width / 2,
      srcCanvas.height / 2 - dstCanvas.height / 2,
      dstCanvas.width,
      dstCanvas.height,
      0,
      0,
      $w,
      $h
    ); //Draw Cropped in center of image
    dstContext.save();
    dstContext.globalCompositeOperation = "overlay";
    dstContext.filter = "blur(.3px)";
    var ptrn = dstContext.createPattern(nseCanvas, "repeat");
    dstContext.fillStyle = ptrn;
    dstContext.fillRect(0, 0, dstCanvas.width, dstCanvas.height);
    dstContext.restore();
    var $inputAndImgSrc = $temp.hasAttribute("data-upload")
      ? $temp.getAttribute("data-upload")
      : $temp.closest("input");
    $inputAndImgSrc = $inputAndImgSrc.split(",");

    console.log($inputAndImgSrc, "nani?");

    //        var $imgThumbnail    = ($($temp).data('thumb') ? $($temp).data('thumb') : '').split(',');

    forEach($inputAndImgSrc, function (i, v) {
      if (document.querySelector(v).tagName == "INPUT") {
        document.querySelector(v).value = dstCanvas.toDataURL("image/jpeg");
      } else if (document.querySelector(v).tagName == "IMG") {
        // document.querySelector(v).setAttribute('src', dstCanvas.toDataURL('image/jpeg'));
      }
    });
    $temp.closest("form").querySelector('[type="submit"]').click();
  });
}

/***********************************************************
 *
 *  ACTUAL CODES, using everything above
 *
 ***********************************************************/
//  function postAndReplace(url, method, e, $data = "") {

//     var xhr = new XMLHttpRequest();
//     // var data = serialize(e.target);

//     var form = e.target.closest("form");
//     var pre = new FormData(form);

//     var data = serialize(e.target);
//     // var data = serializeMike(pre);
//     var targets = e.target.dataset.push.split(',');

//     console.log(data);
//     xhr.open(method, (method == 'POST' ? url : url  /* + '?' + data*/), true);
//     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
//     xhr.send((method == 'POST' ? data : ''));

//     xhr.onreadystatechange = function () {
//         console.log(url, method, data, xhr.status);
//         if (xhr.readyState === 4) {
//             var response = createElementFromHTML(xhr.response);
//             forEach(targets, function (index, value) {

//                 var target = document.querySelector(value);
//                 var rTarget = response.querySelector(value);

//                 if (target.tagName == 'INPUT') {
//                     //INPUT / VALUE
//                     target.value = rTarget.value;
//                 } else {
//                     //ANYTHING ELSE
//                     target.innerHTML = rTarget.innerHTML;
//                 }

//             });

//             if (xhr.status == "202") {
//                 setTimeout(function () {
//                     // window.location.href = '?mod=dashboard';
//                     window.location.href = window.location.href;
//                 }, 2000)
//             }
//         }
//     }
//     e.preventDefault();
// }

function postAndReplace(e) {
  //   console.log(e.target);
  if (e.target.dataset.push) {
    var etarget = e.target,
      emethod = "method",
      eaction = "data-action";
    var seriali = serialize(e.target); //Turns [inputs="text"] to ?query=line&
    var method =
      hasAttr(etarget, emethod) && doesVarExist(getAttr(etarget, emethod))
        ? getAttr(etarget, emethod)
        : "POST";
    var url =
      hasAttr(etarget, eaction) && doesVarExist(getAttr(etarget, eaction))
        ? getAttr(etarget, eaction)
        : window.location.href;
    var data;
    var targets = e.target.dataset.push.split(",");
    var urls = method == "POST" ? url : url; /*+ '?' + data*/
    var xhr = new XMLHttpRequest();

    // formData.append('ss', 'QQ');

    if (e.target.tagName == "FORM") {
      var form = etarget;
      var formData = new FormData(form);
      console.log(formData);
      data = formData;
    } else if (e.target.tagName == "A") {
      data = e.target.href;
    }

    xhr.open(method, urls, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    // xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

    xhr.send(data);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        var response = createElementFromHTML(xhr.response);
        forEach(targets, function (index, value) {
          var target = document.querySelector(value);
          var rTarget = response.querySelector(value);
          if (target && rTarget) {
            forEach(targets, function (index, value) {
              tween({
                from: 0,
                to: 1,
                duration: 550,
                onUpdate: (v) => {
                  target.style.opacity = v;
                },
                onComplete: (v) => {
                  target.style.opacity = null;
                },
              });
            });
            if (target.tagName == "INPUT") {
              //INPUT / VALUE
              target.value = rTarget.value;
            } else {
              //ANYTHING ELSE
              target.innerHTML = rTarget.innerHTML;
            }
          }
        });
        if (xhr.status == "202") {
          setTimeout(function () {
            // window.location.href = '?mod=dashboard';
            window.location.href = window.location.href;
          }, 2000);
        }
      }
    };
    e.preventDefault();
  }
}

// //Draging stuffs with touch and mouse

// var test = document.getElementById('side');
// var toggleside = document.getElementById('toggleSide');

// toggleside.addEventListener('longtap', openSide);

// test.addEventListener('swipeleft', openSide);
// test.addEventListener('swiperight', openSide);

// function openSide(e) {
//     e.preventDefault();

//     var type = e.type;
//     var currX = e.x ? e.x : e.touches[0].clientX;
//     var currY = e.y ? e.y : e.touches[0].clientY;
//     var distanceX = e.distance ? e.distance.x : null;
//     var distanceY = e.distance ? e.distance.y : null;

//     console.log(type, currX, currY, distanceX, distanceY);

//     if (type == 'swipeleft') {
//         toggleside.checked = false;
//         // toggleside.click();
//     } else if (type == 'swiperight') {
//         toggleside.checked = true;
//         // toggleside.click();
//     }

//     return false;
// }

// function updateHtml(e) {
//     e.preventDefault();
//     // eventName.innerHTML = test.innerHTML = e.type;
//     // currX.innerHTML = e.x ? e.x : e.touches[0].clientX;
//     // currY.innerHTML = e.y ? e.y : e.touches[0].clientY;
//     // distanceX.innerHTML = e.distance ? e.distance.x : 'not available';
//     // distanceY.innerHTML = e.distance ? e.distance.y : 'not available';

//     console.log(e.type, 'x:' + e.x ? e.x : e.touches[0].clientX, 'y:' + e.y ? e.y : e.touches[0].clientY, 'distanceX:' + e.distance ? e.distance.x : 'not available', 'distanceY:' + e.distance ? e.distance.y : 'not available');
//     return false;
// }

// var handler = document.querySelector('.handler');
// var wrapper = handler.closest('.details');
// var boxA = wrapper.querySelector('.bx');
// var isHandlerDragging = false;

// document.addEventListener('mousedown', function (e) {
//     // If mousedown event is fired from .handler, toggle flag to true
//     if (e.target === handler) {
//         isHandlerDragging = true;
//     }
// });

// document.addEventListener('mousemove', function (e) {
//     // Don't do anything if dragging flag is false
//     if (!isHandlerDragging) {
//         return false;
//     }

//     // Get offset
//     var containerOffsetLeft = wrapper.offsetLeft;

//     // Get x-coordinate of pointer relative to container
//     var pointerRelativeXpos = e.clientX - containerOffsetLeft;

//     // Arbitrary minimum width set on box A, otherwise its inner content will collapse to width of 0
//     var boxAminWidth = 60;

//     // Resize box A
//     // * 8px is the left/right spacing between .handler and its inner pseudo-element
//     // * Set flex-grow to 0 to prevent it from growing
//     boxA.style.width = (Math.max(boxAminWidth, pointerRelativeXpos - 8)) + 'px';
//     boxA.style.flexGrow = 0;
// });

// document.addEventListener('mouseup', function (e) {
//     // Turn off dragging flag when user mouse is up
//     isHandlerDragging = false;
// });

document
  .querySelectorAll("[data-tiny-editor]")
  .forEach((editor) => editor.addEventListener("input", (e) => reportText(e)));
function reportText(e) {
  if (e.target.hasAttribute("data-edit")) {
    var target = document.querySelector(e.target.getAttribute("data-edit"));
    target.value = e.target.innerHTML;
  } else if (e.target.hasAttribute("data-edit-closest")) {
    var closestTarget = e.target.closest(
      e.target.getAttribute("data-edit-closest")
    );
    console.log(e.target.closest("input"));
  }
}

function reportReady(ele) {
  if (ele.hasAttribute("data-edit")) {
    var target = document.querySelector(ele.getAttribute("data-edit"));
    target.value = ele.innerHTML;
  } else if (ele.hasAttribute("data-edit-closest")) {
    var closestTarget = ele.closest(ele.getAttribute("data-edit-closest"));
    console.log(ele.closest("input"));
  }
}

//On dom ready
document.addEventListener("DOMContentLoaded", function (e) {
  console.log("event", e.type);
  document
    .querySelectorAll("[data-tiny-editor]")
    .forEach((editor) => reportReady(editor));
});

// var cm2 = new ContextMenu('.minimal', items, { className: 'ContextMenu--theme-custom', minimalStyling: true });

// cm1.on('shown', () => console.log('Context menu shown'));

// const clickHandler = e => {
//     e.label.innerText = e.label.innerText.split('').reverse().join('');
//     e.data.text = e.label.innerText;
//     e.handled = true;
// }

// const figure = document.querySelectorAll('figure');

// const contextMenu = new ContextMenu(document.body, [
//     { text: 'Back', hotkey: 'Alt+Left arrow', disabled: false, onclick: clickHandler }
// ]);

// contextMenu.install();

function generate(length = 8) {
  //READABLE RANDOM PASS
  function randomItem(array) {
    return array[Math.floor(Math.random() * array.length)];
  }
  var letters = [
    "bcdfghjklmnpqrstvwxz", // consonnes
    "aeiouy", //voyelles
  ];
  var quantity = 1,
    i,
    j,
    res = "",
    curKey;
  for (i = 0; i < quantity; i++) {
    curKey = Math.floor(Math.random() * letters.length);
    for (j = 0; j < length; j++) {
      res += randomItem(letters[curKey]);
      curKey = (curKey + 1) % letters.length;
    }
    res += "\n";
  }
  return res.trim();
}
