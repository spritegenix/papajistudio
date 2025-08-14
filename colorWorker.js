// public/colorWorker.js

importScripts(
  'https://cdn.jsdelivr.net/npm/colorthief@2.3.2/dist/color-thief.min.js'
);

const colorThief = new ColorThief();

self.onmessage = function (event) {
  const { imageUrl } = event.data;

  try {
    const img = new Image();
    img.crossOrigin = 'anonymous'; // CORS is essential for pixel access
    img.src = imageUrl;

    img.onload = () => {
      const dominantColor = colorThief.getColor(img);
      self.postMessage({ imageUrl, dominantColor });
    };

    img.onerror = () => {
      self.postMessage({
        imageUrl,
        dominantColor: null,
        error: 'Image load failed',
      });
    };
  } catch (error) {
    self.postMessage({ imageUrl, dominantColor: null, error: error.message });
  }
};
