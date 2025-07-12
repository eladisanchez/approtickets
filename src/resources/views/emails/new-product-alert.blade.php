<p>L'usuari {{ $product->organizer->name }} ha creat un producte nou pendent de validació:</p>

<p><a href="{{ route('filament.admin.resources.products.edit', ['record' => $product->id]) }}">{{ $product->title }}</a>
</p>
