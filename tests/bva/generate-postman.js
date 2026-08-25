const fs = require('fs');
const path = require('path');
const bva = require('./bva-cases');

const baseUrl = 'http://localhost/movie-ticket-booking/backend/api.php';

function buildBody(moduleKey, testCase) {
  switch (moduleKey) {
    case 'seat':
      return {
        room_id: 1,
        seat_row: 'A',
        seat_number: testCase.value,
        seat_type_id: 1,
        is_active: true
      };

    case 'room':
      return {
        theatre_id: 1,
        name: `BVA Room ${testCase.id} {{$randomInt}}`,
        total_seats: testCase.value,
        is_active: true
      };

    case 'theatre':
      return {
        name: `BVA Theatre ${testCase.id} {{$randomInt}}`,
        address: '123 BVA Test',
        city: 'Ho Chi Minh',
        phone: '0123456789',
        total_screens: testCase.value
      };

    case 'review':
      return {
        user_id: 1,
        movie_id: 1,
        rating: testCase.value,
        comment: `BVA test ${testCase.id}`
      };

    default:
      return {};
  }
}

function buildRequest(moduleKey, config, testCase) {
  const body = buildBody(moduleKey, testCase);

  return {
    name:
      `${testCase.id} | ${config.field}=${testCase.value} | ${testCase.position}`,

    request: {
      method: config.method,

      header: [
        {
          key: 'Content-Type',
          value: 'application/json'
        }
      ],

      body: {
        mode: 'raw',
        raw: JSON.stringify(body, null, 2)
      },

      url: {
        raw: `{{baseUrl}}${config.endpoint}`,
        host: ['{{baseUrl}}'],
        path: config.endpoint.split('/').filter(Boolean)
      },

      description:
        `TC=${testCase.id}\n` +
        `Module=${config.module}\n` +
        `Field=${config.field}\n` +
        `Value=${testCase.value}\n` +
        `Boundary=${testCase.position}\n` +
        `Expected=${testCase.expected}`
    },

    event: [
      {
        listen: 'test',
        script: {
          type: 'text/javascript',
          exec: [
            `const tcId = "${testCase.id}";`,
            `const field = "${config.field}";`,
            `const inputValue = ${JSON.stringify(testCase.value)};`,
            `const expected = "${testCase.expected}";`,
            '',
            'let json;',
            '',
            'try {',
            '  json = pm.response.json();',
            '} catch (e) {',
            '  json = {};',
            '}',
            '',
            'const actual = json.status || "NO_STATUS";',
            '',
            'console.log("--------------------------------");',
            'console.log("TC:", tcId);',
            'console.log("Field:", field);',
            'console.log("Input:", inputValue);',
            'console.log("Expected:", expected);',
            'console.log("Actual:", actual);',
            '',
            'pm.test(`${tcId} | Expected=${expected} | Actual=${actual}`, function () {',
            '  pm.expect(actual).to.eql(expected);',
            '});'
          ]
        }
      }
    ]
  };
}

const collection = {
  info: {
    name: 'Movie Ticket Booking - BVA Automation',
    description:
      'Collection được sinh tự động từ tests/bva/bva-cases.js',
    schema:
      'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
  },

  variable: [
    {
      key: 'baseUrl',
      value: baseUrl
    }
  ],

  item: []
};

for (const [moduleKey, config] of Object.entries(bva)) {
  const folder = {
    name: `BVA - ${config.module}`,
    item: []
  };

  for (const testCase of config.cases) {
    folder.item.push(
      buildRequest(moduleKey, config, testCase)
    );
  }

  collection.item.push(folder);
}

const outputDir = path.join(__dirname, '..', 'postman');

fs.mkdirSync(outputDir, {
  recursive: true
});

const outputFile = path.join(
  outputDir,
  'BVA_MovieBooking.postman_collection.json'
);

fs.writeFileSync(
  outputFile,
  JSON.stringify(collection, null, 2),
  'utf8'
);

const totalCases = Object.values(bva)
  .reduce((sum, item) => sum + item.cases.length, 0);

console.log('====================================');
console.log('POSTMAN BVA COLLECTION GENERATED');
console.log('====================================');
console.log(`Total Test Cases : ${totalCases}`);
console.log(`Output           : ${outputFile}`);
console.log('====================================');
