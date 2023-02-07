/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  prefix: 'tw-',
  theme: {
    extend: {
      colors: {
        primary: "#ba895d",
        secondary: "#148df6",
        success: "#51bb25",
        info: "#7a15f7",
        warning: "#ff5f24",
        danger: "#fd2e64",
        light: "#e8ebf2",
        dark: "#2c323f"
      }
    },
  },
  plugins: [],
}
