<div class="header">
    <table style="width: 100%; border: none !important;">
        <tr>
            <td style="width: 15%; border: none !important; vertical-align: top;">
                @php
                    $logoPath = public_path('image/logo/logo.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height: 70px;">
                @else
                    <div style="height: 70px; width: 70px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 10px;">LOGO</div>
                @endif
            </td>
            <td style="width: 55%; border: none !important; text-align: center; vertical-align: middle;">
                <h1 style="margin: 0; color: #0665d0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px;">G-SANTÉ</h1>
                <p style="margin: 5px 0 0; font-size: 10px; color: #666; line-height: 1.4;">
                    <strong>Clinique Médicale & Centre d'Excellence</strong><br>
                    Tél: (+225) 07 00 00 00 00 / 05 00 00 00 00<br>
                    E-mail: contact@g-sante.com | Web: www.g-sante.com<br>
                    Abidjan, Côte d'Ivoire
                </p>
            </td>
            <td style="width: 30%; border: none !important; text-align: right; vertical-align: top;">
                <div style="font-size: 11px; color: #333; background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6;">
                    <strong style="color: #0665d0;">Date:</strong> {{ now()->format('d/m/Y') }}<br>
                    <strong style="color: #0665d0;">Heure:</strong> {{ now()->format('H:i') }}<br>
                    <strong style="color: #0665d0;">Doc N°:</strong> {{ $docNumber ?? 'N/A' }}
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 3px; background: #0665d0; margin: 15px 0 5px;"></div>
    <div style="height: 1px; background: #0665d0; margin-bottom: 20px; opacity: 0.3;"></div>
</div>
