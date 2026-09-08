const fs = require('fs');
const path = require('path');
const newman = require('newman');
const bvaCases = require('../bva/bva-cases');

const collectionPath = path.resolve(
  __dirname,
  '../postman/BVA_MovieBooking.postman_collection.json'
);

const reportDir = path.resolve(
  __dirname,
  '../reports'
);

fs.mkdirSync(reportDir, {
  recursive: true
});

// ========================================
// Map TC ID -> BVA information
// ========================================

const testCaseMap = new Map();

for (const config of Object.values(bvaCases)) {
  for (const testCase of config.cases) {
    testCaseMap.set(testCase.id, {
      module: config.module,
      field: config.field,
      input: testCase.value,
      boundary: testCase.position,
      expected: testCase.expected
    });
  }
}

// Cho phép chạy riêng folder:
// node tests/automation/run-bva-and-log.js "BVA - Review"
const folder = process.argv[2];

const options = {
  collection: collectionPath,
  reporters: []
};

if (folder) {
  options.folder = [folder];
}

console.log('==========================================');
console.log('BVA AUTOMATION - RESULT LOGGING');
console.log('==========================================');

if (folder) {
  console.log(`Folder : ${folder}`);
} else {
  console.log('Folder : ALL BVA TESTS');
}

console.log('==========================================');

newman.run(options, (error, summary) => {
  if (error) {
    console.error('Không thể chạy Newman:');
    console.error(error.message);

    process.exitCode = 1;
    return;
  }

  const results = [];

  for (const execution of summary.run.executions) {
    const itemName = execution.item.name;

    const tcId = itemName
      .split('|')[0]
      .trim();

    const meta = testCaseMap.get(tcId);

    if (!meta) {
      continue;
    }

    let actual = 'NO_STATUS';

    try {
      if (execution.response?.stream) {
        const body = JSON.parse(
          execution.response.stream.toString()
        );

        actual = body.status || 'NO_STATUS';
      }
    } catch (e) {
      actual = 'INVALID_JSON';
    }

    const failedAssertions = (
      execution.assertions || []
    ).filter(assertion => assertion.error);

    const result =
      actual === meta.expected &&
      failedAssertions.length === 0
        ? 'PASS'
        : 'FAIL';

    const errorMessage = failedAssertions
      .map(assertion =>
        assertion.error?.message || ''
      )
      .filter(Boolean)
      .join(' | ');

    results.push({
      testCaseId: tcId,
      module: meta.module,
      field: meta.field,
      input: meta.input,
      boundary: meta.boundary,
      expected: meta.expected,
      actual,
      httpStatus:
        execution.response?.code || null,
      responseTimeMs:
        execution.response?.responseTime || null,
      result,
      error: errorMessage || null
    });
  }

  // ========================================
  // Summary
  // ========================================

  const total = results.length;

  const passed = results.filter(
    item => item.result === 'PASS'
  ).length;

  const failed = results.filter(
    item => item.result === 'FAIL'
  ).length;

  const timestamp = new Date()
    .toISOString()
    .replace(/[:.]/g, '-');

  const report = {
    generatedAt: new Date().toISOString(),
    collection:
      'Movie Ticket Booking - BVA Automation',
    folder: folder || 'ALL',
    summary: {
      total,
      passed,
      failed,
      passRate:
        total === 0
          ? 0
          : Number(
              ((passed / total) * 100).toFixed(2)
            )
    },
    results
  };

  // ========================================
  // JSON Result Log
  // ========================================

  const jsonFile = path.join(
    reportDir,
    `bva-result-${timestamp}.json`
  );

  const latestJsonFile = path.join(
    reportDir,
    'latest-bva-result.json'
  );

  fs.writeFileSync(
    jsonFile,
    JSON.stringify(report, null, 2),
    'utf8'
  );

  fs.writeFileSync(
    latestJsonFile,
    JSON.stringify(report, null, 2),
    'utf8'
  );

  // ========================================
  // CSV Result Log
  // ========================================

  const csvHeader = [
    'TestCaseID',
    'Module',
    'Field',
    'Input',
    'Boundary',
    'Expected',
    'Actual',
    'HTTPStatus',
    'ResponseTimeMs',
    'Result',
    'Error'
  ];

  const escapeCsv = value => {
    if (value === null || value === undefined) {
      return '';
    }

    const text = String(value)
      .replace(/"/g, '""');

    return `"${text}"`;
  };

  const csvRows = results.map(item => [
    item.testCaseId,
    item.module,
    item.field,
    item.input,
    item.boundary,
    item.expected,
    item.actual,
    item.httpStatus,
    item.responseTimeMs,
    item.result,
    item.error || ''
  ].map(escapeCsv).join(','));

  const csvContent = [
    csvHeader.join(','),
    ...csvRows
  ].join('\n');

  const csvFile = path.join(
    reportDir,
    `bva-result-${timestamp}.csv`
  );

  const latestCsvFile = path.join(
    reportDir,
    'latest-bva-result.csv'
  );

  fs.writeFileSync(
    csvFile,
    csvContent,
    'utf8'
  );

  fs.writeFileSync(
    latestCsvFile,
    csvContent,
    'utf8'
  );

  // ========================================
  // Console Result Log
  // ========================================

  console.log('');

  for (const item of results) {
    const symbol =
      item.result === 'PASS'
        ? '[PASS]'
        : '[FAIL]';

    console.log(
      `${symbol} ${item.testCaseId}` +
      ` | ${item.field}=${item.input}` +
      ` | Expected=${item.expected}` +
      ` | Actual=${item.actual}`
    );
  }

  console.log('');
  console.log('==========================================');
  console.log('BVA TEST RESULT SUMMARY');
  console.log('==========================================');
  console.log(`Total    : ${total}`);
  console.log(`Passed   : ${passed}`);
  console.log(`Failed   : ${failed}`);
  console.log(`Pass Rate: ${report.summary.passRate}%`);
  console.log('------------------------------------------');
  console.log(`JSON     : ${jsonFile}`);
  console.log(`CSV      : ${csvFile}`);
  console.log('==========================================');

  // Automation phải trả exit code fail
  // nếu có ít nhất 1 TC FAIL.
  if (failed > 0) {
    process.exitCode = 1;
  }
});