document
  .getElementById("upload-form")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    var fileInput = document.getElementById("file-input");
    var file = fileInput.files[0];

    if (!file) {
      return;
    }

    var metadata = {
      name: file.name,
      filename: file.name,
      filetype: file.type,
      maxDurationSeconds: -50,
    };

    var upload = new tus.Upload(file, {
      endpoint: "/upload",
      retryDelays: [0, 1000, 3000, 5000],
      metadata,
      onProgress: function (bytesUploaded, bytesTotal) {
        var percentage = ((bytesUploaded / bytesTotal) * 100).toFixed(2);
        document.querySelector(".progress-bar").style.width = percentage + "%";
        document
          .querySelector(".progress-bar")
          .setAttribute("aria-valuenow", percentage);
        document.querySelector(".progress-bar").textContent = percentage + "%";
      },
      onSuccess: function (response) {
        const videoId = getVideoIdFromUrl(upload.url)

        alert("Video uploaded successfully!");
        fileInput.value = "";
        document.getElementById("progress-container").style.display = "none";
      },
      onError: function (error) {
        console.log("Error uploading video: " + error.message);
        document.getElementById("progress-container").style.display = "none";
      },
    });

    upload.start();
    document.getElementById("progress-container").style.display = "block";
  });

  function getVideoIdFromUrl(url) {
    var videoId = url.split('?')[0].split('/').pop();
    return videoId;
  }
