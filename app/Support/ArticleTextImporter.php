<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PharData;
use PharException;
use SimpleXMLElement;
use UnexpectedValueException;

class ArticleTextImporter
{
    public function extract(UploadedFile $file): string
    {
        $text = match (Str::lower($file->getClientOriginalExtension())) {
            'txt' => $this->extractTextFile($file),
            'docx' => $this->extractWordDocument($file),
            default => throw ValidationException::withMessages([
                'article_file' => 'Format dokumen tidak didukung. Gunakan file TXT atau DOCX.',
            ]),
        };

        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($text === '') {
            throw ValidationException::withMessages([
                'article_file' => 'Dokumen tidak berisi teks artikel yang dapat dibaca.',
            ]);
        }

        if (Str::length($text) > 50000) {
            throw ValidationException::withMessages([
                'article_file' => 'Isi artikel maksimal 50.000 karakter.',
            ]);
        }

        return $text;
    }

    private function extractTextFile(UploadedFile $file): string
    {
        $text = file_get_contents($file->getRealPath());

        if ($text === false) {
            throw ValidationException::withMessages([
                'article_file' => 'File teks tidak dapat dibaca.',
            ]);
        }

        return preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;
    }

    private function extractWordDocument(UploadedFile $file): string
    {
        try {
            $archive = new PharData($file->getRealPath());

            if (! $archive->offsetExists('word/document.xml')) {
                throw ValidationException::withMessages([
                    'article_file' => 'File DOCX tidak memiliki dokumen Word yang dapat dibaca.',
                ]);
            }

            $document = $archive['word/document.xml'];

            if ($document->getSize() > 5 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'article_file' => 'Konten dokumen Word terlalu besar untuk diimpor.',
                ]);
            }

            $xmlContents = $document->getContent();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (UnexpectedValueException|PharException|\BadMethodCallException $exception) {
            throw ValidationException::withMessages([
                'article_file' => 'File DOCX tidak valid atau tidak dapat dibaca.',
            ]);
        }

        if (! is_string($xmlContents)) {
            throw ValidationException::withMessages([
                'article_file' => 'Konten dokumen Word tidak dapat dibaca.',
            ]);
        }

        $previousLibxmlSetting = libxml_use_internal_errors(true);

        try {
            $documentXml = simplexml_load_string(
                $xmlContents,
                SimpleXMLElement::class,
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }

        if (! $documentXml instanceof SimpleXMLElement) {
            throw ValidationException::withMessages([
                'article_file' => 'Struktur teks di dalam file DOCX tidak valid.',
            ]);
        }

        $documentXml->registerXPathNamespace(
            'w',
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
        );
        $paragraphs = $documentXml->xpath('//w:body//w:p');

        if ($paragraphs === false) {
            throw ValidationException::withMessages([
                'article_file' => 'Paragraf di dalam file DOCX tidak dapat dibaca.',
            ]);
        }

        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $textNodes = $paragraph->xpath('.//w:t | .//w:tab | .//w:br | .//w:cr');

            if ($textNodes === false) {
                continue;
            }

            $line = '';

            foreach ($textNodes as $textNode) {
                $line .= match ($textNode->getName()) {
                    'tab' => "\t",
                    'br', 'cr' => "\n",
                    default => (string) $textNode,
                };
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
