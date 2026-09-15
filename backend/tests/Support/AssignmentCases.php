<?php
namespace Tests\Support;

use RuntimeException;

/** Load only design tables (never mapping tables) from the versioned reports. */
final class AssignmentCases
{
    public static function read(string $module, array $groups): array
    {
        $path = dirname(__DIR__, 3) . '/docs/testing/modules/' . strtolower($module) . '/' . $module . '_BaoCao_Form_Assignment.md';
        $text = file_get_contents($path);
        preg_match_all('/^# \d+\. METHOD \d+.*?`(\w+)\([^\n]*\n(.*?)(?=^# |\z)/msu', $text, $methods, PREG_SET_ORDER);
        $cases = [];
        foreach ($methods as $method) {
            if (!preg_match('/## (\d+)\.4\. Bước 3.*?\n(.*?)(?=## \1\.5\.)/su', $method[2], $design)) {
                throw new RuntimeException('Missing design section: ' . $method[1]);
            }
            $headers = null;
            foreach (explode("\n", $design[2]) as $line) {
                if (preg_match('/^\| STT\s*\|/', $line)) {
                    $headers = self::cells($line);
                } elseif (preg_match('/^\| ((?:EP|BVA|LB|TB)-\d{2})\s*\|/', $line, $id)) {
                    $cells = self::cells($line);
                    if (!$headers || count($cells) !== count($headers)) {
                        throw new RuntimeException('Malformed case ' . $id[1]);
                    }
                    $key = $method[1] . '/' . $id[1];
                    if (isset($cases[$key])) {
                        throw new RuntimeException('Duplicate case ' . $key);
                    }
                    $cases[$key] = [$method[1], $id[1], array_combine($headers, $cells)];
                }
            }
        }
        $required = [];
        foreach ($groups as $method => $counts) {
            foreach ($counts as $group => $count) {
                for ($i = 1; $i <= $count; $i++) {
                    $required[] = $method . '/' . $group . '-' . sprintf('%02d', $i);
                }
            }
        }
        if (array_keys($cases) !== $required) {
            throw new RuntimeException('Missing, reordered or unexpected design cases in ' . $module);
        }
        return $cases;
    }

    private static function cells(string $line): array
    {
        return array_map('trim', explode('|', trim(trim($line), '|')));
    }

    public static function value(string $cell)
    {
        $cell = str_replace('`', '', $cell);
        if (preg_match("/^'([^']*)'(?: \\(chuỗi\\))?$/u", $cell, $m)) return $m[1];
        if ($cell === 'true') return true;
        if ($cell === 'false') return false;
        if (preg_match('/^-?\d+$/', $cell)) return (int)$cell;
        if (str_starts_with($cell, '[')) return json_decode($cell, true, 512, JSON_THROW_ON_ERROR);
        return $cell;
    }

    public static function inputs(array $row, array $names): array
    {
        return array_map(static fn($name) => self::value($row[$name]), $names);
    }

    public static function status(array $row): string
    {
        $expected = $row['Kết quả mong đợi'];
        if (str_starts_with($expected, '**Hợp lệ**')) return 'success';
        if (str_starts_with($expected, '**Không hợp lệ**')) return 'error';
        throw new RuntimeException('Unknown status in case');
    }

    public static function error(array $row): string
    {
        $message = preg_replace('/^\*\*Không hợp lệ\*\* – /u', '', $row['Kết quả mong đợi']);
        return explode(';', $message)[0];
    }
}
