<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
    use this <strong>token</strong> to reset your password <br>
    <input type="text" readonly value="{{ getUrlParamsfromStr($url)['token'] }}" style="
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;">
{{-- <a href="{{ $url }}" class="button button-{{ $color ?? 'primary' }}" target="_blank" rel="noopener">{{ $slot }}</a> --}}
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
