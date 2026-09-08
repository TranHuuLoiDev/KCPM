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
    endpoint: '/theatres/validate',
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
    endpoint: '/reviews/validate',
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
  },


//==========================
    authentication: {
        module: 'Authentication',
        field: 'password',
        endpoint: '/register',
        method: 'POST',
        min: 6,

        cases: [
          {
            id: 'TC-AUTH-BVA-01',
            value: '12345',
            position: 'MIN - 1',
            expected: 'error'
          },
          {
            id: 'TC-AUTH-BVA-02',
            value: '123456',
            position: 'MIN',
            expected: 'success'
          },
          {
            id: 'TC-AUTH-BVA-03',
            value: '1234567',
            position: 'MIN + 1',
            expected: 'success'
          }
        ]
      },
     //=============

    booking: {
      module: 'Booking',

      cases: [
        // ============================================================
        // processBooking.userId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-01",
          serviceMethod: "processBooking",
          field: "userId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-02",
          serviceMethod: "processBooking",
          field: "userId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-03",
          serviceMethod: "processBooking",
          field: "userId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-04",
          serviceMethod: "processBooking",
          field: "userId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // processBooking.showtimeId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-05",
          serviceMethod: "processBooking",
          field: "showtimeId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-06",
          serviceMethod: "processBooking",
          field: "showtimeId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-07",
          serviceMethod: "processBooking",
          field: "showtimeId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-08",
          serviceMethod: "processBooking",
          field: "showtimeId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // processBooking.seatIds
        // ============================================================
        {
          id: "TC-BOOKING-BVA-09",
          serviceMethod: "processBooking",
          field: "seatIds",
          value: [],
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-10",
          serviceMethod: "processBooking",
          field: "seatIds",
          value: [1],
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-11",
          serviceMethod: "processBooking",
          field: "seatIds",
          value: [1, 2],
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // getUserBookings.userId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-12",
          serviceMethod: "getUserBookings",
          field: "userId",
          value: -1,
          boundary: "MIN - 2",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-13",
          serviceMethod: "getUserBookings",
          field: "userId",
          value: 0,
          boundary: "MIN - 1",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-14",
          serviceMethod: "getUserBookings",
          field: "userId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-15",
          serviceMethod: "getUserBookings",
          field: "userId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // cancelBooking.userId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-16",
          serviceMethod: "cancelBooking",
          field: "userId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-17",
          serviceMethod: "cancelBooking",
          field: "userId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-18",
          serviceMethod: "cancelBooking",
          field: "userId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-19",
          serviceMethod: "cancelBooking",
          field: "userId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // cancelBooking.bookingId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-20",
          serviceMethod: "cancelBooking",
          field: "bookingId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-21",
          serviceMethod: "cancelBooking",
          field: "bookingId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-22",
          serviceMethod: "cancelBooking",
          field: "bookingId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-23",
          serviceMethod: "cancelBooking",
          field: "bookingId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // getAdminBookingDetail.bookingId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-24",
          serviceMethod: "getAdminBookingDetail",
          field: "bookingId",
          value: -1,
          boundary: "MIN - 2",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-25",
          serviceMethod: "getAdminBookingDetail",
          field: "bookingId",
          value: 0,
          boundary: "MIN - 1",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-26",
          serviceMethod: "getAdminBookingDetail",
          field: "bookingId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-27",
          serviceMethod: "getAdminBookingDetail",
          field: "bookingId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // updateAdminBookingStatus.bookingId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-28",
          serviceMethod: "updateAdminBookingStatus",
          field: "bookingId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-29",
          serviceMethod: "updateAdminBookingStatus",
          field: "bookingId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-30",
          serviceMethod: "updateAdminBookingStatus",
          field: "bookingId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-31",
          serviceMethod: "updateAdminBookingStatus",
          field: "bookingId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // deleteAdminBooking.bookingId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-32",
          serviceMethod: "deleteAdminBooking",
          field: "bookingId",
          value: -1,
          boundary: "MIN - 2",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-33",
          serviceMethod: "deleteAdminBooking",
          field: "bookingId",
          value: 0,
          boundary: "MIN - 1",
          expected: "error"
        },
        {
          id: "TC-BOOKING-BVA-34",
          serviceMethod: "deleteAdminBooking",
          field: "bookingId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-35",
          serviceMethod: "deleteAdminBooking",
          field: "bookingId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        },

        // ============================================================
        // getTotalSpentByUser.userId
        // ============================================================
        {
          id: "TC-BOOKING-BVA-36",
          serviceMethod: "getTotalSpentByUser",
          field: "userId",
          value: -1,
          boundary: "MIN - 2",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-37",
          serviceMethod: "getTotalSpentByUser",
          field: "userId",
          value: 0,
          boundary: "MIN - 1",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-38",
          serviceMethod: "getTotalSpentByUser",
          field: "userId",
          value: 1,
          boundary: "MIN",
          expected: "success"
        },
        {
          id: "TC-BOOKING-BVA-39",
          serviceMethod: "getTotalSpentByUser",
          field: "userId",
          value: 2,
          boundary: "MIN + 1",
          expected: "success"
        }
      ]
    }
};
