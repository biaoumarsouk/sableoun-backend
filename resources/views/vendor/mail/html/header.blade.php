{{-- Fichier: resources/views/vendor/mail/html/header.blade.php (Corrigé) --}}
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block; color: #3d4852; font-size: 19px; font-weight: bold; text-decoration: none;">
            {{-- On affiche directement le nom de l'application depuis le .env --}}
            {{ config('app.name') }}
        </a>
    </td>
</tr>