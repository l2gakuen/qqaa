if (doesExist("#drop-area")) {
  // ************************ Drag and drop ***************** //
  const dropArea = document.getElementById("drop-area");

  // Prevent default drag behaviors
  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropArea.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
  });

  // Highlight drop area when item is dragged over it
  //   ["dragenter", "dragover"].forEach((eventName) => {
  //     dropArea.addEventListener(eventName, highlight, false);
  //   });
  //   ["dragleave", "drop"].forEach((eventName) => {
  //     dropArea.addEventListener(eventName, unhighlight, false);
  //   });

  // Handle dropped files
  dropArea.addEventListener("drop", handleDrop, false);

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  function highlight(e) {
    dropArea.classList.add("highlight");
  }

  function unhighlight(e) {
    dropArea.classList.remove("active");
  }

  function handleDrop(e) {
    var dt = e.dataTransfer;
    var files = dt.files;
    let input = document.getElementById("documentFile");
    input.files = files;
    handleFiles(files);
  }

  let uploadProgress = [];
  let progressBar = document.getElementById("progress-bar");

  function initializeProgress(numFiles) {
    progressBar.value = 0;
    uploadProgress = [];

    for (let i = numFiles; i > 0; i--) {
      uploadProgress.push(0);
    }
  }

  function updateProgress(fileNumber, percent) {
    uploadProgress[fileNumber] = percent;
    let total =
      uploadProgress.reduce((tot, curr) => tot + curr, 0) /
      uploadProgress.length;
    progressBar.value = total;
  }

  function handleFiles(files) {
    files = [...files];
    // initializeProgress(files.length);
    files.forEach(uploadFile);
    files.forEach(prepareFiles);
    // files.forEach(previewFile)
  }

  function prepareFiles(file) {
    let reader = new FileReader();

    let fileName = document.getElementById("fileName");
    let fileContent = document.getElementById("fileContent");
    let prepare = document.getElementById("prepare");
    let select = prepare.querySelector("select");
    let submit = document.querySelector(".submitter");
    let drop = document.getElementById("drop");

    reader.readAsDataURL(file);
    reader.onloadend = function () {
      // let img = document.createElement('img')
      // img.src = reader.result
      fileName.value = file.name.replace(/\.[a-zA-Z0-9]+$/, "");
      // prepare.classList.remove('d-none');
      // select.classList.remove('d-none');
      // submit.classList.remove('d-none');
      // drop.classList.add('d-none');
      select.disabled = false;
      console.log(file);
    };
  }

  function previewFile(file) {
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function () {
      let img = document.createElement("img");
      img.src = reader.result;
      document.getElementById("gallery").appendChild(img);
    };
  }

  function uploadFile(file, i) {
    var dropArea = document.getElementById("drop-area");
    var url = window.location.href;
    var xhr = new XMLHttpRequest();
    var form = dropArea.querySelector("form");
    var formData = new FormData(form);

    let blob_export;
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function (e) {
      /*********************************************
       *
       *     RESIZE BEFORE UPLOAD
       *
       */

      var $img = document.createElement("img");
      $img.src = e.target.result;
      $img.onload = function (i) {
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
              "rgba(" +
              number +
              "," +
              number +
              "," +
              number +
              "," +
              opacity +
              ")";
            nseContext.fillRect(x, y, 1, 1);
          }
        }
        //Resize and Crop
        var $w = 1920;
        var $h = 1440;
        $portraitToLandscape =
          i.target.height < i.target.width
            ? i.target.height / i.target.width
            : i.target.width / i.target.height;
        srcCanvas.width = $w;
        // srcCanvas.height = srcCanvas.width * (i.target.height / i.target.width); //Ratio
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

        // console.log(dstCanvas.toDataURL('image/jpeg'));
        blob_export = dstCanvas.toDataURL("image/jpeg");

        // console.log(blob_export);
        /*************************/
        xhr.open("POST", url, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

        // Update progress (can be used to show progress indicator)
        // xhr.upload.addEventListener("progress", function (e) {
        //   updateProgress(i, (e.loaded * 100.0) / e.total || 100);
        // });

        console.log(formData);
        xhr.addEventListener("readystatechange", function (e) {
          if (xhr.readyState == 4 && xhr.status == 200) {
            // updateProgress(i, 100); // <- Add this
            if (form.hasAttribute("data-upload")) {
              var targets = form.dataset.upload.split(",");
              var response = createElementFromHTML(xhr.response);
              forEach(targets, function (index, value) {
                var target = document.querySelector(value);
                var rTarget = response.querySelector(value);
                // console.log(target, rTarget);
                if (rTarget != null) {
                  if (target.tagName == "INPUT") {
                    //INPUT / VALUE
                    target.value = rTarget.value;
                  } else {
                    //ANYTHING ELSE
                    target.innerHTML = rTarget.innerHTML;
                  }
                }
              });
            }
          } else if (xhr.readyState == 4 && xhr.status != 200) {
            // Error. Inform the user
          }
        });
        // console.log(blob_export);
        formData.append("file_blob", blob_export);
        formData.append("action", "upload");
        formData.append("file", file);
        // console.log(formData);
        setTimeout(() => {
          xhr.send(formData);
        }, 1000 * i);
      };
    };
  }
}
