// tailwind.config.js
module.exports = {
    content: [
        "./index.html",
        "./src/**/*.{html,js}"
    ],
    theme: {
      extend: {
        scrollSnapType: {
          y: "y mandatory",
        },
        scrollSnapAlign: {
          start: "start",
        },
      },
    },
    plugins: [
      function ({ addUtilities }) {
        addUtilities({
          '.scroll-snap-y': {
            scrollSnapType: 'y mandatory',
          },
          '.scroll-snap-align-start': {
            scrollSnapAlign: 'start',
          },
        });
      },
    ],
  };
  