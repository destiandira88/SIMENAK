<?php

namespace App\Libraries\Qa;

class BlackBoxReport
{
    /** @var list<array{modul:string,langkah:string,diharapkan:string,sebenarnya:string,status:string}> */
    private array $rows = [];

    /** @var list<string> */
    private array $notes = [];

    public function add(
        string $modul,
        string $langkah,
        string $diharapkan,
        string $sebenarnya,
        string $status
    ): void {
        $this->rows[] = [
            'modul'      => $modul,
            'langkah'    => $langkah,
            'diharapkan' => $diharapkan,
            'sebenarnya' => $sebenarnya,
            'status'     => strtoupper($status),
        ];
    }

    public function note(string $text): void
    {
        $this->notes[] = $text;
    }

    /**
     * @return list<array{modul:string,langkah:string,diharapkan:string,sebenarnya:string,status:string}>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    public function toMarkdown(): string
    {
        $ok   = 0;
        $bug  = 0;
        $info = 0;
        foreach ($this->rows as $r) {
            if ($r['status'] === 'OK') {
                $ok++;
            } elseif ($r['status'] === 'BUG') {
                $bug++;
            } else {
                $info++;
            }
        }
        $total = count($this->rows);

        $md  = "# Laporan QA Black Box — SIMENAK\n\n";
        $md .= 'Tanggal: ' . date('Y-m-d H:i:s') . " WIB\n\n";
        $md .= "## Ringkasan\n\n";
        $md .= "| Metrik | Jumlah |\n|---|---:|\n";
        $md .= "| Total kasus diuji | {$total} |\n";
        $md .= "| Lolos (OK) | {$ok} |\n";
        $md .= "| Bug (BUG) | {$bug} |\n";
        $md .= "| Info / tidak bisa diuji penuh (INFO) | {$info} |\n\n";

        $bugs = array_values(array_filter($this->rows, static fn ($r) => $r['status'] === 'BUG'));
        if ($bugs !== []) {
            $md .= "## Daftar Bug (kritis → ringan)\n\n";
            $i = 1;
            foreach ($bugs as $b) {
                $md .= "{$i}. **[{$b['modul']}]** {$b['langkah']} — {$b['sebenarnya']}\n";
                $i++;
            }
            $md .= "\n";
        }

        if ($this->notes !== []) {
            $md .= "## Catatan teknis\n\n";
            foreach ($this->notes as $n) {
                $md .= "- {$n}\n";
            }
            $md .= "\n";
        }

        $md .= "## Detail kasus\n\n";
        $md .= "| Modul | Langkah yang dilakukan | Hasil yang diharapkan | Hasil sebenarnya | Status |\n";
        $md .= "|---|---|---|---|---|\n";
        foreach ($this->rows as $r) {
            $md .= '| ' . $this->esc($r['modul'])
                . ' | ' . $this->esc($r['langkah'])
                . ' | ' . $this->esc($r['diharapkan'])
                . ' | ' . $this->esc($r['sebenarnya'])
                . ' | **' . $r['status'] . "** |\n";
        }

        return $md;
    }

    private function esc(string $s): string
    {
        return str_replace(["\n", '|'], [' ', '\\|'], trim($s));
    }
}
