const fs = require('fs');
const path = require('path');
const bva = require('./bva-cases');

const baseUrl = 'http://localhost/movie-ticket-booking/backend/api.php';



function getRequestConfig(testCase) {
  switch (testCase.serviceMethod) {
    case 'processBooking':
      return {
        method: 'POST',
        endpoint: '/bookings/validate'
      };

    case 'getUserBookings':
      return {
        method: 'GET',
        endpoint: '/bookings/validate-user'
      };

    case 'cancelBooking':
      return {
        method: 'POST',
        endpoint: '/bookings/validate-cancel'
      };

    case 'getAdminBookingDetail':
      return {
        method: 'GET',
        endpoint: '/admin/bookings/validate-detail'
      };

    case 'updateAdminBookingStatus':
      return {
        method: 'POST',
        endpoint: '/admin/bookings/validate-status'
      };

    case 'deleteAdminBooking':
      return {
        method: 'DELETE',
        endpoint: '/admin/bookings/validate-delete'
      };

    case 'getTotalSpentByUser':
      return {
        method: 'GET',
        endpoint: '/bookings/validate-total-spent'
      };

    default:
      throw new Error(
        `Unsupported BookingService method: ${testCase.serviceMethod}`
      );
  }
}


function buildBody(moduleKey, testCase) {
  switch (moduleKey) {

    case 'authentication':
      return {
        first_name: 'BVA',
        last_name: 'Authentication',
        email: `bva.auth.${testCase.id}.{{$randomInt}}@example.com`,
        phone: `09{{$randomInt}}`,
        birth_date: '2000-01-01',
        password: testCase.value,
        confirm_password: testCase.value
      };

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

    case 'booking': {
      switch (testCase.serviceMethod) {

        case 'processBooking':
          return {
            user_id:
              testCase.field === 'userId'
                ? testCase.value
                : 1,

            showtime_id:
              testCase.field === 'showtimeId'
                ? testCase.value
                : 1,

            seat_ids:
              testCase.field === 'seatIds'
                ? testCase.value
                : [1],

            payment_method: 'cash'
          };

        case 'getUserBookings':
          return {
            user_id: testCase.value
          };

        case 'cancelBooking':
          return {
            user_id:
              testCase.field === 'userId'
                ? testCase.value
                : 1,

            booking_id:
              testCase.field === 'bookingId'
                ? testCase.value
                : 1
          };

        case 'getAdminBookingDetail':
          return {
            booking_id: testCase.value
          };

        case 'updateAdminBookingStatus':
          return {
            booking_id: testCase.value,
            status: 'paid'
          };

        case 'deleteAdminBooking':
          return {
            booking_id: testCase.value
          };

        case 'getTotalSpentByUser':
          return {
            user_id: testCase.value
          };

        default:
          return {};
      }
    }

    default:
      return {};
  }
}


function buildRequest(moduleKey, config, testCase) {
  const requestConfig =
    moduleKey === 'booking'
      ? getRequestConfig(testCase)
      : {
          method: config.method,
          endpoint: config.endpoint
        };

  const body = buildBody(moduleKey, testCase);

  const isGet =
    requestConfig.method === 'GET';

  const isDelete =
    requestConfig.method === 'DELETE';

  let urlRaw =
    `{{baseUrl}}${requestConfig.endpoint}`;

  if (
    moduleKey === 'booking' &&
    (
      testCase.serviceMethod === 'getUserBookings' ||
      testCase.serviceMethod === 'getTotalSpentByUser'
    )
  ) {
    urlRaw += `?user_id=${encodeURIComponent(testCase.value)}`;
  }

  if (
    moduleKey === 'booking' &&
    testCase.serviceMethod === 'getAdminBookingDetail'
  ) {
    urlRaw += `?booking_id=${encodeURIComponent(testCase.value)}`;
  }

  const request = {
    method: requestConfig.method,

    header: [
      {
        key: 'Content-Type',
        value: 'application/json'
      }
    ],

    url: {
      raw: urlRaw,
      host: ['{{baseUrl}}'],
      path: requestConfig.endpoint
        .split('/')
        .filter(Boolean)
    }
  };

  if (!isGet && !isDelete) {
    request.body = {
      mode: 'raw',
      raw: JSON.stringify(body, null, 2)
    };
  }

  return {
    name:
      `${testCase.id} | ` +
      `${testCase.serviceMethod} | ` +
      `${testCase.field}=${JSON.stringify(testCase.value)} | ` +
      `${testCase.boundary}`,

    request,

    event: [
      {
        listen: 'test',
        script: {
          type: 'text/javascript',

          exec: [
            `const tcId = "${testCase.id}";`,
            `const serviceMethod = "${testCase.serviceMethod}";`,
            `const field = "${testCase.field}";`,
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
            'console.log("Service:", serviceMethod);',
            'console.log("Field:", field);',
            'console.log("Input:", inputValue);',
            'console.log("Expected:", expected);',
            'console.log("Actual:", actual);',

            '',

            'pm.test(`${tcId} | ${serviceMethod} | Expected=${expected} | Actual=${actual}`, function () {',
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
