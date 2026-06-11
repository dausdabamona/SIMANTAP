<table style="width:100%;border-bottom:3px solid #000;padding-bottom:8px;margin-bottom:12px;">
    <tr>
        <td style="width:80px;vertical-align:middle;text-align:center;">
            <img src="{{ public_path('images/logo-kkp.png') }}" style="height:70px;" onerror="this.style.display='none'">
        </td>
        <td style="text-align:center;vertical-align:middle;padding:0 10px;">
            <div style="font-size:10pt;font-weight:bold;text-transform:uppercase;">{{ config('simantap.kop_surat.baris1') }}</div>
            <div style="font-size:9pt;font-weight:bold;text-transform:uppercase;">{{ config('simantap.kop_surat.baris2') }}</div>
            <div style="font-size:11pt;font-weight:bold;text-transform:uppercase;">{{ config('simantap.kop_surat.baris3') }}</div>
            <div style="font-size:8pt;margin-top:2px;">
                {{ config('simantap.kop_surat.alamat') }}
                &nbsp;|&nbsp; Telp. {{ config('simantap.kop_surat.telp') }}
                &nbsp;|&nbsp; {{ config('simantap.kop_surat.website') }}
            </div>
        </td>
        <td style="width:80px;vertical-align:middle;text-align:center;">
            <img src="{{ public_path('images/logo-poltek.png') }}" style="height:70px;" onerror="this.style.display='none'">
        </td>
    </tr>
</table>
