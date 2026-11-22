<?php

declare(strict_types=1);

namespace Cine\Poster;

/**
 * Generates an original art-deco style SVG poster for a movie from its
 * title, genre, rating and hue. No third-party artwork is used.
 */
final class PosterGenerator
{
    private const WIDTH = 400;
    private const HEIGHT = 600;

    /**
     * @param array{movie_id: int|string, title: string, genre: string, rating: string, poster_hue: int|string} $movie
     */
    public function render(array $movie): string
    {
        $hue = ((int) $movie['poster_hue']) % 360;
        $accent = ($hue + 40) % 360;
        $seed = (int) $movie['movie_id'];
        $title = $this->wrap(mb_strtoupper((string) $movie['title']), 12);
        $titleSize = $this->fontSize($title);

        $rays = '';
        $count = 18 + $seed % 6;
        for ($i = 0; $i < $count; $i++) {
            $angle = -90 + ($i - ($count - 1) / 2) * (150 / $count);
            $rad = deg2rad($angle);
            $x = 200 + cos($rad) * 520;
            $y = 330 + sin($rad) * 520;
            $opacity = $i % 2 === 0 ? '0.16' : '0.07';
            $rays .= sprintf(
                '<line x1="200" y1="330" x2="%.1f" y2="%.1f" stroke="hsl(%d 90%% 70%%)" stroke-width="%d" opacity="%s"/>',
                $x,
                $y,
                $accent,
                $i % 3 === 0 ? 10 : 4,
                $opacity,
            );
        }

        $lines = '';
        $startY = 420 - (count($title) - 1) * ($titleSize * 0.55);
        foreach ($title as $index => $line) {
            $lines .= sprintf(
                '<text x="200" y="%.1f" text-anchor="middle" font-size="%d" font-weight="700" letter-spacing="2" fill="#f7ecd6">%s</text>',
                $startY + $index * ($titleSize * 1.08),
                $titleSize,
                $this->xml($line),
            );
        }

        $circles = '';
        for ($i = 0; $i < 3; $i++) {
            $circles .= sprintf(
                '<circle cx="200" cy="330" r="%d" fill="none" stroke="hsl(%d 80%% 72%%)" stroke-opacity="%.2f" stroke-width="2"/>',
                70 + $i * 34 + ($seed % 4) * 6,
                $accent,
                0.45 - $i * 0.12,
            );
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 600" width="400" height="600" role="img" aria-label="Afiche de {$this->xml((string) $movie['title'])}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="hsl({$hue} 55% 22%)"/>
      <stop offset="0.6" stop-color="hsl({$hue} 60% 12%)"/>
      <stop offset="1" stop-color="hsl({$hue} 40% 6%)"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.5" cy="0.55" r="0.5">
      <stop offset="0" stop-color="hsl({$accent} 90% 65%)" stop-opacity="0.55"/>
      <stop offset="1" stop-color="hsl({$accent} 90% 65%)" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="400" height="600" fill="url(#bg)"/>
  <g>{$rays}</g>
  <circle cx="200" cy="330" r="190" fill="url(#glow)"/>
  {$circles}
  <rect x="14" y="14" width="372" height="572" fill="none" stroke="hsl({$accent} 70% 70%)" stroke-opacity="0.6" stroke-width="2"/>
  <rect x="22" y="22" width="356" height="556" fill="none" stroke="hsl({$accent} 70% 70%)" stroke-opacity="0.25" stroke-width="1"/>
  <g font-family="Georgia, 'Times New Roman', serif">
    <text x="200" y="74" text-anchor="middle" font-size="14" letter-spacing="6" fill="hsl({$accent} 80% 78%)">BUTACA PRESENTA</text>
    <text x="200" y="104" text-anchor="middle" font-size="18" font-style="italic" fill="#f7ecd6" opacity="0.85">{$this->xml((string) $movie['genre'])}</text>
    {$lines}
  </g>
  <g font-family="Arial, Helvetica, sans-serif">
    <rect x="166" y="528" width="68" height="28" rx="14" fill="none" stroke="#f7ecd6" stroke-opacity="0.7"/>
    <text x="200" y="547" text-anchor="middle" font-size="14" font-weight="700" fill="#f7ecd6">{$this->xml((string) $movie['rating'])}</text>
  </g>
</svg>
SVG;
    }

    /**
     * Splits a title into lines of at most `$width` characters (by words).
     *
     * @return list<string>
     */
    public function wrap(string $text, int $width): array
    {
        $lines = [];
        $current = '';
        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($current !== '' && mb_strlen($candidate) > $width) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, 4);
    }

    /**
     * Largest font size (px) that fits the longest line in the poster width.
     *
     * @param list<string> $lines
     */
    public function fontSize(array $lines): int
    {
        $longest = max(1, ...array_map('mb_strlen', $lines ?: ['']));

        return (int) max(22, min(46, floor(320 / ($longest * 0.74))));
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
