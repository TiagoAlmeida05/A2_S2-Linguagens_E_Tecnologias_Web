document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const videoInput = document.getElementById('videoInput');

    if (imageInput) {
        imageInput.addEventListener('change', function (event) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = ''; 
            const files = event.target.files;
            if (files.length === 0) return;

            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;

                const img = document.createElement('img');
                img.style.maxWidth = '150px';
                img.style.margin = '10px 10px 10px 0';
                img.style.borderRadius = '8px';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            }
        });
    }

    if (videoInput) {
        videoInput.addEventListener('change', function (event) {
            const preview = document.getElementById('videoPreview');
            preview.innerHTML = '';

            const files = event.target.files;
            if (files.length === 0) return;

            for (const file of files) {
                if (!file.type.startsWith('video/')) continue;

                const video = document.createElement('video');
                video.controls = true;
                video.style.maxWidth = '300px';
                video.style.margin = '10px 10px 10px 0';
                video.style.borderRadius = '8px';
                video.src = URL.createObjectURL(file);
                preview.appendChild(video);
            }
        });
    }
});
