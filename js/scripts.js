var contextImage = [
  // { name: 'Cut', fn: function (target) { console.log('Cut', target); } },
  // { name: 'Copy', fn: function (target) { console.log('Copy', target); } },
  // { name: 'Paste', fn: function (target) { console.log('Paste', target); } },
  // {},
  // { name: 'Select All', fn: function (target) { console.log('Select All', target); } },
  {
    name: "Effacer image",
    fn: function (target) {
      // var form = target.closest('form');
      var targets = target.dataset.post.split(",");
      var xhr = new XMLHttpRequest();
      var formData = new FormData();

      formData.append("action", "removeImage");
      formData.append("role", target.dataset.role);
      formData.append("iid", target.dataset.uri);

      xhr.open("POST", window.location.href, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      // xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

      xhr.send(formData);
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

          // document.querySelector('#checkbar').innerHTML = response.querySelector('#checkbar').innerHTML;

          // console.log(response);
          // alert('QQ')
        }
      };
    },
  },
];
var contextAgenda = [
  // { name: 'Cut', fn: function (target) { console.log('Cut', target); } },
  // { name: 'Copy', fn: function (target) { console.log('Copy', target); } },
  // { name: 'Paste', fn: function (target) { console.log('Paste', target); } },
  // {},
  // { name: 'Select All', fn: function (target) { console.log('Select All', target); } },
  {
    name: "Effacer Evènement",
    fn: function (target) {
      var targets = target.dataset.post.split(",");
      var xhr = new XMLHttpRequest();
      var formData = new FormData();

      formData.append("action", "supprimer");
      formData.append("eid", target.dataset.item);

      xhr.open("POST", window.location.href, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      // xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

      xhr.send(formData);
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

          // document.querySelector('#checkbar').innerHTML = response.querySelector('#checkbar').innerHTML;

          // console.log(response);
          // alert('QQ')
        }
      };
    },
  },
];
var contextUser = [
  {
    name: "Retirer l'adhérent ",
    fn: function (target) {
      var xhr = new XMLHttpRequest();
      var formData = new FormData();
      var targets = target.dataset.post.split(",");

      formData.append("action", "removeUser");
      formData.append("uid", target.dataset.uid);
      formData.append('eid', target.dataset.eid);

      xhr.open("POST", target.dataset.action, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      // xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

      xhr.send(formData);
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
    },
  },
];
var contextGroup = [
  {
    name: "Retirer",
    fn: function (target) {
      var xhr = new XMLHttpRequest();
      var formData = new FormData();
      var targets = target.dataset.post.split(",");

      formData.append("action", "remove2Group");
      formData.append("fid", target.dataset.group);
      formData.append('aid', target.dataset.aid);

      xhr.open("POST", target.dataset.action, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      // xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

      xhr.send(formData);
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
    },
  },
];

var cm1 = new ContextMenu("figure", contextImage);
var cm2 = new ContextMenu(".event", contextAgenda);
var cm3 = new ContextMenu(".status.user", contextUser);
var cm4 = new ContextMenu(".trctx", contextGroup);