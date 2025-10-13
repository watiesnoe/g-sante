<?php
require __DIR__ . "/../../vendor/autoload.php";
use League\CommonMark\GithubFlavoredMarkdownConverter;
[$_, $src, $out] = $argv;
$c = new GithubFlavoredMarkdownConverter(['html_input' => 'escape']);
$md = file_get_contents($src);
$body = (string) $c->convert($md);
$body = preg_replace('/href="(\d\d-[a-z-]+)\.md"/', 'href="$1.pdf"', $body);
$css = <<<CSS
@page { size: A4; margin: 16mm 15mm 18mm; }
body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11pt; line-height: 1.45; color: #1a1a1a; }
h1 { font-size: 19pt; color: #0b5d8f; border-bottom: 3px solid #0b5d8f; padding-bottom: 6px; margin-top: 0; }
h2 { font-size: 14pt; color: #0b5d8f; margin-top: 20px; break-after: avoid; }
table { border-collapse: collapse; width: 100%; margin: 8px 0; font-size: 10pt; break-inside: avoid; }
th, td { border: 1px solid #bbb; padding: 5px 7px; text-align: left; vertical-align: top; }
th { background: #e8f1f7; }
code { background: #f2f2f2; padding: 1px 4px; border-radius: 3px; font-size: 9.5pt; }
pre { background: #f4f4f4; border-left: 3px solid #0b5d8f; padding: 8px 10px; font-size: 9pt; white-space: pre-wrap; break-inside: avoid; }
pre code { background: none; padding: 0; }
blockquote { border-left: 4px solid #d9822b; background: #fdf3e8; margin: 10px 0; padding: 6px 12px; }
li { margin: 2px 0; }
a { color: #0b5d8f; text-decoration: none; }
.pied { margin-top: 24px; font-size: 8.5pt; color: #777; border-top: 1px solid #ddd; padding-top: 4px; }
CSS;
$date = date('d/m/Y');
file_put_contents($out, "<!doctype html><html lang=\"fr\"><head><meta charset=\"utf-8\"><style>$css</style></head><body>$body<div class=\"pied\">G-SANTÉ — guide d'utilisation, version du $date</div></body></html>");
