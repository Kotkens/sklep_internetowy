<?php
/** Prosta strona 404 */
get_header(); ?>
<main class="not-found-simple" style="max-width:760px;margin:80px auto 120px;padding:40px 34px;background:#fff;border:1px solid #e2e8f0;border-radius:18px;text-align:center;box-shadow:0 8px 28px -10px rgba(0,0,0,.08);">
    <h1 style="font-size:52px;line-height:1;margin:0 0 6px;font-weight:700;color:#0f172a;">404</h1>
    <h2 style="font-size:22px;margin:0 0 18px;font-weight:600;color:#1e293b;">Strona nie została znaleziona</h2>
    <p style="margin:0 0 28px;font-size:15px;color:#475569;">Żądana strona nie istnieje lub została przeniesiona.</p>
    <a href="<?php echo esc_url( home_url('/') ); ?>" style="display:inline-block;padding:14px 28px;background:#FF6B00;color:#fff;font-weight:600;border-radius:10px;text-decoration:none;box-shadow:0 4px 14px -4px rgba(0,0,0,.25);">Wróć na stronę główną</a>
</main>
<?php get_footer(); ?>
