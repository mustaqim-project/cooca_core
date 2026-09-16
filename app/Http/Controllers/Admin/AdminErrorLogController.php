<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AdminErrorLogController extends Controller
{
    /**
     * Display the error logs view with parsed diagnostic records.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $logDir = storage_path('logs');
        $files = $this->getAvailableLogFiles($logDir);
        $filesMetadata = $this->formatLogFilesMetadata($files);

        // Determine active log file (safe from path traversal)
        $selectedFile = basename((string) $request->input('file', $files[0] ?? ('laravel-' . date('Y-m-d') . '.log')));
        if (!in_array($selectedFile, $files, true)) {
            $selectedFile = $files[0] ?? ('laravel-' . date('Y-m-d') . '.log');
        }

        $activeFilePath = $logDir . DIRECTORY_SEPARATOR . $selectedFile;
        $parsedLogs = $this->parseLogFile($activeFilePath);

        // Pre-filter statistics for Bento KPI cards
        $stats = [
            'total' => count($parsedLogs),
            'errors' => count(array_filter($parsedLogs, fn(array $l): bool => in_array($l['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'], true))),
            'warnings' => count(array_filter($parsedLogs, fn(array $l): bool => $l['level'] === 'WARNING')),
            'info' => count(array_filter($parsedLogs, fn(array $l): bool => in_array($l['level'], ['INFO', 'NOTICE', 'DEBUG'], true))),
        ];

        // Apply Level Filter
        if ($request->filled('level') && $request->input('level') !== 'all') {
            $filterLevel = strtoupper((string) $request->input('level'));
            if ($filterLevel === 'ERROR') {
                $parsedLogs = array_filter($parsedLogs, fn(array $l): bool => in_array($l['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'], true));
            } else {
                $parsedLogs = array_filter($parsedLogs, fn(array $l): bool => $l['level'] === $filterLevel);
            }
        }

        // Apply Search Filter (message, exception class, stack trace, or timestamp)
        if ($request->filled('search')) {
            $search = strtolower((string) $request->input('search'));
            $parsedLogs = array_filter($parsedLogs, static function (array $l) use ($search): bool {
                return str_contains(strtolower($l['message']), $search)
                    || str_contains(strtolower($l['stack']), $search)
                    || str_contains(strtolower($l['timestamp']), $search)
                    || str_contains(strtolower($l['level']), $search);
            });
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'file' => $selectedFile,
                'files' => $files,
                'files_metadata' => $filesMetadata,
                'stats' => $stats,
                'logs' => array_values($parsedLogs),
            ]);
        }

        $fileSize = File::exists($activeFilePath) ? round(File::size($activeFilePath) / 1024, 2) : 0.0;

        return view('admin.error-logs.index', [
            'logs' => array_values($parsedLogs),
            'files' => $files,
            'filesMetadata' => $filesMetadata,
            'selectedFile' => $selectedFile,
            'stats' => $stats,
            'fileSize' => $fileSize,
        ]);
    }

    /**
     * Clear the content of the currently selected log file.
     */
    public function clear(Request $request): RedirectResponse
    {
        $logDir = storage_path('logs');
        $defaultFile = 'laravel-' . date('Y-m-d') . '.log';
        $selectedFile = basename((string) $request->input('file', $defaultFile));
        $filePath = $logDir . DIRECTORY_SEPARATOR . $selectedFile;

        if (File::exists($filePath)) {
            File::put($filePath, '');
            return back()->with('success', "Berkas log [{$selectedFile}] berhasil dikosongkan.");
        }

        return back()->with('error', "Berkas log [{$selectedFile}] tidak ditemukan.");
    }

    /**
     * Download the raw log file.
     */
    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $logDir = storage_path('logs');
        $defaultFile = 'laravel-' . date('Y-m-d') . '.log';
        $selectedFile = basename((string) $request->input('file', $defaultFile));
        $filePath = $logDir . DIRECTORY_SEPARATOR . $selectedFile;

        if (!File::exists($filePath)) {
            return back()->with('error', "Berkas log [{$selectedFile}] tidak ditemukan.");
        }

        return response()->download($filePath, $selectedFile, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * List all log files in storage/logs sorted by date descending.
     *
     * @return list<string>
     */
    private function getAvailableLogFiles(string $dir): array
    {
        $todayFile = 'laravel-' . date('Y-m-d') . '.log';
        $logFiles = [];

        if (File::isDirectory($dir)) {
            $allFiles = File::files($dir);
            foreach ($allFiles as $file) {
                if ($file->getExtension() === 'log') {
                    $filename = $file->getFilename();
                    if (preg_match('/^laravel-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                        $logFiles[$filename] = strtotime($matches[1]) ?: $file->getMTime();
                    } else {
                        $logFiles[$filename] = $file->getMTime();
                    }
                }
            }
        }

        // Always ensure today's daily log file is available
        if (!isset($logFiles[$todayFile])) {
            $logFiles[$todayFile] = strtotime(date('Y-m-d')) ?: time();
        }

        arsort($logFiles);

        return array_keys($logFiles);
    }

    /**
     * Format log filenames into humanized metadata for display in the UI.
     *
     * @param list<string> $files
     * @return array<string, array{name: string, label: string, is_today: bool, is_yesterday: bool}>
     */
    private function formatLogFilesMetadata(array $files): array
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $result = [];

        foreach ($files as $file) {
            $label = "📁 {$file}";
            $isToday = false;
            $isYesterday = false;

            if (preg_match('/^laravel-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $dateStr = $m[1];
                $timestamp = strtotime($dateStr);
                $formattedDate = $timestamp !== false ? date('d M Y', $timestamp) : $dateStr;

                if ($dateStr === $today) {
                    $label = "📅 Hari Ini - {$formattedDate} ({$file})";
                    $isToday = true;
                } elseif ($dateStr === $yesterday) {
                    $label = "📅 Kemarin - {$formattedDate} ({$file})";
                    $isYesterday = true;
                } else {
                    $label = "📁 {$formattedDate} ({$file})";
                }
            } elseif ($file === 'laravel.log') {
                $label = '📁 laravel.log (Single Log)';
            } elseif (str_starts_with($file, 'cron')) {
                $label = "⚙️ {$file} (Scheduler Log)";
            }

            $result[$file] = [
                'name' => $file,
                'label' => $label,
                'is_today' => $isToday,
                'is_yesterday' => $isYesterday,
            ];
        }

        return $result;
    }

    /**
     * Parse log file entries efficiently with streaming bounds to prevent OOM.
     *
     * @return list<array{id: int, timestamp: string, env: string, level: string, message: string, stack: string}>
     */
    private function parseLogFile(string $filePath): array
    {
        if (!File::exists($filePath) || File::size($filePath) === 0) {
            return [];
        }

        $logs = [];
        $currentLog = null;
        $logId = 1;

        try {
            $file = new \SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            // Limit to reading the last 3000 lines to guarantee optimal latency & prevent OOM
            $startLine = max(0, $totalLines - 3000);
            $file->seek($startLine);

            while (!$file->eof()) {
                $line = (string) $file->fgets();
                if (trim($line) === '') {
                    continue;
                }

                // Standard Monolog Pattern: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message
                if (preg_match('/^\[(?P<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<env>\w+)\.(?P<level>[A-Z]+): (?P<message>.*)/', $line, $matches)) {
                    if ($currentLog !== null) {
                        $logs[] = $currentLog;
                    }

                    $currentLog = [
                        'id' => $logId++,
                        'timestamp' => $matches['date'],
                        'env' => $matches['env'],
                        'level' => $matches['level'],
                        'message' => trim($matches['message']),
                        'stack' => '',
                    ];
                } elseif ($currentLog !== null) {
                    $currentLog['stack'] .= $line;
                }
            }

            if ($currentLog !== null) {
                $logs[] = $currentLog;
            }
        } catch (\Throwable $e) {
            return [];
        }

        // Return newest log entries first
        return array_reverse($logs);
    }
}
