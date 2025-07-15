// Requires Color Thief (https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.2/color-thief.umd.js)
// Usage: dynamicCardShadow('.ttgr-item-inner img', 0.18, '0 4px 24px')

function dynamicCardShadow(
  imgSelector,
  alpha = 0.7,
  shadowSpec = '0 15px 24px'
) {
  if (typeof ColorThief === 'undefined') {
    console.error('ColorThief is not loaded.');
    return;
  }
  var colorThief = new ColorThief();
  document.querySelectorAll(imgSelector).forEach(function (img) {
    function setShadow() {
      try {
        var color = colorThief.getColor(img);
        var shadow = `${shadowSpec} rgba(${color[0]},${color[1]},${color[2]},${alpha})`;
        var card = img.closest('.shadow-image');
        if (card) card.style.boxShadow = shadow;
      } catch (e) {
        // fallback shadow
        var card = img.closest('.shadow-image');
        if (card) card.style.boxShadow = `${shadowSpec} rgba(234,181,197,0.10)`;
      }
    }
    if (img.complete) {
      setShadow();
    } else {
      img.addEventListener('load', setShadow);
    }
  });
}
// Example auto-run for gallery cards:
document.addEventListener('DOMContentLoaded', function () {
  dynamicCardShadow('.ttgr-item-inner img');
});
