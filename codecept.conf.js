/** @type {CodeceptJS.MainConfig} */
exports.config = {
  tests: 'tests/e2e/*_test.js',
  output: 'output',
  helpers: {
    Playwright: {
      browser: 'chromium',
      url: 'http://localhost/movie-ticket-booking',
      show: true
    }
  },
  include: {
    I: './steps_file.js'
  },
  noGlobals: true,
  plugins: {},
  name: 'movie-ticket-booking'
}