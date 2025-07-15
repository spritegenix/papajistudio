// Responsive video source swap utility
// Usage: Add data-src-desktop and data-src-mobile to any <video> element

function swapVideoSource(video, options) {
  var source = video.querySelector('source');
  var src = options.desktop;
  if (window.innerWidth <= (options.breakpoint || 768)) {
    src = options.mobile;
  }
  if (source.getAttribute('src') !== src) {
    source.setAttribute('src', src);
    video.load();
  }
}

function autoSwapVideoSource(video) {
  swapVideoSource(video, {
    desktop: video.getAttribute('data-src-desktop'),
    mobile: video.getAttribute('data-src-mobile'),
    breakpoint: video.getAttribute('data-breakpoint') || 768,
  });
}

function setupResponsiveVideos() {
  var videos = document.querySelectorAll(
    'video[data-src-desktop][data-src-mobile]'
  );
  videos.forEach(function (video) {
    autoSwapVideoSource(video);
    window.addEventListener('resize', function () {
      autoSwapVideoSource(video);
    });
  });
}

window.addEventListener('DOMContentLoaded', setupResponsiveVideos);
