module.exports = {
  seat: {
    module: 'Seat',
    field: 'seat_number',
    endpoint: '/seats/validate',
    method: 'POST',
    min: 1,
    max: 12,

    cases: [
      {
        id: 'TC-SEAT-BVA-01',
        value: 0,
        position: 'MIN - 1',
        expected: 'error'
      },
      {
        id: 'TC-SEAT-BVA-02',
        value: 1,
        position: 'MIN',
        expected: 'success'
      },
      {
        id: 'TC-SEAT-BVA-03',
        value: 2,
        position: 'MIN + 1',
        expected: 'success'
      },
      {
        id: 'TC-SEAT-BVA-04',
        value: 11,
        position: 'MAX - 1',
        expected: 'success'
      },
      {
        id: 'TC-SEAT-BVA-05',
        value: 12,
        position: 'MAX',
        expected: 'success'
      },
      {
        id: 'TC-SEAT-BVA-06',
        value: 13,
        position: 'MAX + 1',
        expected: 'error'
      }
    ]
  },

  room: {
    module: 'Room',
    field: 'total_seats',
    endpoint: '/rooms/validate',
    method: 'POST',
    min: 1,

    cases: [
      {
        id: 'TC-ROOM-BVA-01',
        value: -1,
        position: 'MIN - 2',
        expected: 'error'
      },
      {
        id: 'TC-ROOM-BVA-02',
        value: 0,
        position: 'MIN - 1',
        expected: 'error'
      },
      {
        id: 'TC-ROOM-BVA-03',
        value: 1,
        position: 'MIN',
        expected: 'success'
      },
      {
        id: 'TC-ROOM-BVA-04',
        value: 2,
        position: 'MIN + 1',
        expected: 'success'
      }
    ]
  },

  theatre: {
    module: 'Theatre',
    field: 'total_screens',
    endpoint: '/theatres',
    method: 'POST',
    min: 1,

    cases: [
      {
        id: 'TC-THEATRE-BVA-01',
        value: -1,
        position: 'MIN - 2',
        expected: 'error'
      },
      {
        id: 'TC-THEATRE-BVA-02',
        value: 0,
        position: 'MIN - 1',
        expected: 'error'
      },
      {
        id: 'TC-THEATRE-BVA-03',
        value: 1,
        position: 'MIN',
        expected: 'success'
      },
      {
        id: 'TC-THEATRE-BVA-04',
        value: 2,
        position: 'MIN + 1',
        expected: 'success'
      }
    ]
  },

  review: {
    module: 'Review',
    field: 'rating',
    endpoint: '/reviews',
    method: 'POST',
    min: 1,
    max: 5,

    cases: [
      {
        id: 'TC-REVIEW-BVA-01',
        value: 0,
        position: 'MIN - 1',
        expected: 'error'
      },
      {
        id: 'TC-REVIEW-BVA-02',
        value: 1,
        position: 'MIN',
        expected: 'success'
      },
      {
        id: 'TC-REVIEW-BVA-03',
        value: 2,
        position: 'MIN + 1',
        expected: 'success'
      },
      {
        id: 'TC-REVIEW-BVA-04',
        value: 4,
        position: 'MAX - 1',
        expected: 'success'
      },
      {
        id: 'TC-REVIEW-BVA-05',
        value: 5,
        position: 'MAX',
        expected: 'success'
      },
      {
        id: 'TC-REVIEW-BVA-06',
        value: 6,
        position: 'MAX + 1',
        expected: 'error'
      }
    ]
  }
};
