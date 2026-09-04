<?php

declare(strict_types=1);

namespace App\Support\Excel;

use RuntimeException;
use ZipArchive;

/**
 * Penulis file Excel (.xlsx) berdiri sendiri berbasis OOXML + ZipArchive bawaan PHP.
 * Mendukung: multi-sheet, gaya (header/total/section/number), formula, merge cell,
 * kolom otomatis, freeze pane, dan autoFilter — tanpa dependency Composer.
 */
final class XlsxWriter
{
    /** Daftar style sel — indeks ini dipakai langsung di styles.xml (cellXfs). */
    public const STYLE_NORMAL =  ​0;
    public const STYLE_BOLD =  ​1;
    public const STYLE_HEADER =  ​2;
    public const STYLE_TITLE =  ​3;
    public const STYLE_SECTION =  ​4;
    public const STYLE_TOTAL =  ​5;
    public const STYLE_NUMBER =  ​6;
    public const STYLE_NUMBER_DECIMAL =  ​7;

    /** @var array<int, array{name: string, rows: array<int, array<int, mixed>>, widths?: array<string, int|float>, freeze?: string, merge?: string[], autoFilter?: string}> */
    private array $sheets = [];

    private ?string $title = null;
    private ?string $creator = null;

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setCreator(?string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Tambah sheet baru.
     *
     * @param array<int, array<int, mixed>> $rows Baris data; sel bisa scalar
     *   (string/int/float) — angka otomatis pakai style ribuan; atau array:
     *   ['v' => value], ['f' => formula], ['s' => styleId] (style opsional).
     * @param array{widths?: array<string, int|float>, freeze?: string, merge?: string[], autoFilter?: string} $options
     */
    public function addSheet(string $name, array $rows = [], array $options = []): self
    {
        $this->sheets[] = [
            'name'  => $this->sanitizeSheetName($name),
            'rows'   => $rows,
            'widths' => $options['widths'] ?? [],
            'freeze' => $options['freeze'] ?? null,
            'merge'  => $options['merge'] ?? [],
            'autoFilter' => $options['autoFilter'] ?? null,
        ];

        return $this;
    }

    public function save(string $path): void
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Gagal membuat file Excel: {$path}");
        }

        $sheetCount = count($this->sheets);

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes($sheetCount));
        $zip->addFromString('_rels/.rels', $this->buildRootRels());
        $zip->addFromString('docProps/core.xml', $this->buildCoreProps());
        $zip->addFromString('docProps/app.xml', $this->buildAppProps($sheetCount));
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels($sheetCount));
        $zip->addFromString('xl/styles.xml', self::buildStylesXml());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString("xl/worksheets/sheet" . ($i + 1) . ".xml", $this->buildSheetXml($sheet));
        }

        $zip->close();
    }

    /** --------------------------------------------------------------------- *
     * Bagian XML
     * ---------------------------------------------------------------- */

    private function buildContentTypes(int $sheetCount): string
    {
        $xml = self::xmlHeader() . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .='<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .='<Default Extension="xml" ContentType="application/xml"/>';
        $xml .='<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
        $xml .='<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
        $xml .='<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .='<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $xml .="<Override PartName=\"/xl/worksheets/sheet{$i}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
        }

        return $xml . '</Types>';
    }

    private function buildRootRels(): string
    {
        return self::xmlHeader() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function buildCoreProps(): string
    {
        $created = gmdate('Y-m-d\TH:i:s\Z');

        return self::xmlHeader()
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>' . self::esc($this->creator ?? 'Cooca Core') . '</dc:creator>'
            . '<cp:lastModifiedBy>' . self::esc($this->creator ?? 'Cooca Core') . '</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:modified>'
            . ($this->title !== null ? '<dc:title>' . self::esc($this->title) . '</dc:title>' : '')
            . '</cp:coreProperties>';
    }

    private function buildAppProps(int $sheetCount): string
    {
        return self::xmlHeader()
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Cooca Core</Application>'
            . '<TitlesOfParts><vt:vector size="' . $sheetCount . '" baseType="lpstr">'
            . str_repeat('<vt:lpstr>Cooca</vt:lpstr>', $sheetCount)
            . '</vt:vector></TitlesOfParts>'
            . '</Properties>';
    }

    private function buildWorkbook(): string
    {
        $sheetsXml = '';

        foreach ($this->sheets as $i => $sheet) {
            $sheetsXml .='<sheet name="' . self::esc($sheet['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return self::xmlHeader()
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="12000"/></bookViews>'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRels(int $sheetCount): string
    {
        $rels = self::xmlHeader() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .="<Relationship Id=\"rId{$i}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$i}.xml\"/>";
        }

        $rels .='<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return $rels . '</Relationships>';
    }

    private static function buildStylesXml(): string
    {
        return self::xmlHeader()
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2">'
            . '<numFmt numFmtId="164" formatCode="#,##0"/>'
            . '<numFmt numFmtId="165" formatCode="#,##0.00"/>'
            . '</numFmts>'
            . '<fonts count="3">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><color rgb="FFFFFFFF"/><sz val="13"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="6">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF047857"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="8">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function buildSheetXml(array $sheet): string
    {
        $rowsXml = '';
        $maxCol = 0;
        $maxRow = count($sheet['rows']);

        foreach ($sheet['rows'] as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $cellsXml = '';

            foreach ((array) $row as $cIdx => $cell) {
                $colNum = $cIdx + 1;
                $maxCol = max($maxCol, $colNum);
                $ref = $this->colLetter($colNum) . $rowNum;
                $this->appendCellXml($cellsXml, $ref, $cell);
            }

            $rowsXml .="<row r=\"{$rowNum}\">{$cellsXml}</row>";
        }

        $dimension = 'A1:' . $this->colLetter(max(1, $maxCol)) . max(1, $maxRow);

        $xml = self::xmlHeader();
        $xml .='<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .='<dimension ref="' . $dimension . '"/>';
        $xml .='<sheetViews><sheetView workbookViewId="0">' . $this->buildPaneXml($sheet['freeze'] ?? null) . '</sheetView></sheetViews>';
        $xml .='<sheetFormatPr defaultRowHeight="15"/>';

        if (($sheet['widths'] ?? []) !== []) {
            $xml .='<cols>';

            foreach ($sheet['widths'] as $colLetter => $width) {
                $colNum = $this->colNumber((string) $colLetter);
                $xml .="<col min=\"{$colNum}\" max=\"{$colNum}\" width=\"" . self::esc((string) $width) . '" customWidth="1"/>';
            }

            $xml .='</cols>';
        }

        $xml .='<sheetData>' . $rowsXml . '</sheetData>';

        if (($sheet['merge'] ?? []) !== []) {
            $xml .='<mergeCells count="' . count($sheet['merge']) . '">';

            foreach ($sheet['merge'] as $range) {
                $xml .='<mergeCell ref="' . self::esc((string) $range) . '"/>';
            }

            $xml .='</mergeCells>';
        }

        if (($sheet['autoFilter'] ?? null) !== null) {
            $xml .='<autoFilter ref="' . self::esc((string) $sheet['autoFilter']) . '"/>';
        }

        return $xml . '</worksheet>';
    }

    private function buildPaneXml(?string $freeze): string
    {
        if ($freeze === null || ! preg_match('/^([A-Z]+)(\d+)$/', $freeze, $m)) {
            return '';
        }

        $x = max(0, $this->colNumber($m[1]) - 1);
        $y = max(0, (int) $m[2]) - 1;

        if ($x > 0 && $y > 0) {
            return "<pane xSplit=\"{$x}\" ySplit=\"{$y}\" topLeftCell=\"{$freeze}\" activePane=\"bottomRight\" state=\"frozen\"/>";
        }

        if ($x > 0) {
            return "<pane xSplit=\"{$x}\" topLeftCell=\"{$freeze}\" activePane=\"topRight\" state=\"frozen\"/>";
        }

        if ($y > 0) {
            return "<pane ySplit=\"{$y}\" topLeftCell=\"{$freeze}\" activePane=\"bottomLeft\" state=\"frozen\"/>";
        }

        return '';
    }

    private function appendCellXml(string &$xml, string $ref, mixed $cell): void
    {
        if (is_array($cell)) {
            $value = $cell['v'] ?? null;
            $style = (int) ($cell['s'] ?? 0);
            $formula = $cell['f'] ?? null;
        } else {
            $value = $cell;
            $style = 0;
            $formula = null;
        }

        if ($formula === null && is_string($value) && str_starts_with($value, '=')) {
            $formula = ltrim($value, '=');
            $value = null;
        }

        if ($formula !== null) {
            $xml .="<c r=\"{$ref}\" s=\"{$style}\"><f>" . self::esc((string) $formula) . '</f></c>';

            return;
        }

        if ($value === null || $value === '') {
            $xml .="<c r=\"{$ref}\" s=\"{$style}\"/>";

            return;
        }

        if (is_string($value) && $style === 0 && is_numeric($value)) {
            $value = (float) $value;
        }

        if (is_int($value) || is_float($value)) {
            $num = (float) $value;

            if ($style === 0) {
                $style = floor($num) === $num ? self::STYLE_NUMBER : self::STYLE_NUMBER_DECIMAL;
            }

            $normalized = number_format($num, floor($num) === $num ? 0 : 2, '.', '');

            $xml .="<c r=\"{$ref}\" s=\"{$style}\"><v>" . $normalized . '</v></c>';

            return;
        }

        $xml .="<c r=\"{$ref}\" t=\"inlineStr\" s=\"{$style}\"><is><t xml:space=\"preserve\">" . self::esc((string) $value) . '</t></is></c>';
    }

    /** --------------------------------------------------------------------- *
     * Util
     * ---------------------------------------------------------------- */

    private function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\[\]:*?\/\\\\]/u', ' ', $name) ?? '';
        $name = mb_substr(trim($name), 0, 31);

        return $name === '' ? 'Sheet' : $name;
    }

    private function colLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    private function colNumber(string $letters): int
    {
        $num = 0;

        foreach (str_split(strtoupper($letters)) as $ch) {
            $num = $num * 26 + (ord($ch) - 64);
        }

        return $num;
    }

    private static function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}