<?php

/**
 * Ubah isian turut mengundang (baris baru, koma, atau titik koma) menjadi daftar nama berurutan.
 *
 * @return list<string>
 */
function parseTurutMengundangList(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }

    $normalized = preg_replace('/\r\n|\r/', "\n", $raw) ?? $raw;
    $parts      = preg_split('/[\n,;]+/', $normalized) ?: [];
    $items      = [];

    foreach ($parts as $part) {
        $item = trim((string) $part);
        if ($item !== '') {
            $items[] = $item;
        }
    }

    return $items;
}
