<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\NewsPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProductController extends Controller
{
    public function create(Request $request)
    {
        $kind = $request->query('kind') === 'vip' ? 'vip' : 'lock';

        return view('admin.products.form', [
            'product' => new Product([
                'kind' => $kind,
                'duration_days' => $kind === 'vip' ? 30 : 35,
                'sort_order' => (int) Product::query()->where('kind', $kind)->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image_key'] = $data['image_key'] ?: Str::slug($data['name']);
        $data['image_path'] = $this->storeImage($request);

        $product = Product::query()->create($data);

        if (! $product->isVip()) {
            NewsPublisher::forProduct($product, $request->user());
        }

        $message = $product->isVip()
            ? $data['name'].' is now on the VIP board.'
            : $data['name'].' is now on the shop and in News.';

        return redirect()->route('admin.index')->with('success', $message);
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $data['image_key'] = $data['image_key'] ?: $product->image_key ?: Str::slug($data['name']);

        if ($path = $this->storeImage($request)) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $path;
        }

        $product->update($data);

        return redirect()->route('admin.index')->with('success', $product->name.' has been updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchases()->exists()) {
            return back()->with('info', $product->name.' already has client purchases, so it cannot be deleted.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $label = $product->name;
        $product->delete();

        return redirect()->route('admin.index')->with('success', $label.' removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'kind' => $request->input('kind', $product?->kind ?: 'lock'),
        ]);

        $kind = $request->input('kind');
        $isVip = $kind === 'vip';

        $data = $request->validate([
            'kind' => ['required', Rule::in(['lock', 'vip'])],
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', Rule::unique('products', 'code')->ignore($product)],
            'cost_price' => ['required', 'integer', 'min:1'],
            'daily_income' => [$isVip ? 'nullable' : 'required', 'integer', 'min:0'],
            'monthly_salary' => [$isVip ? 'required' : 'nullable', 'integer', 'min:1'],
            'member_requirement' => [$isVip ? 'required' : 'nullable', 'integer', 'min:0'],
            'color' => [$isVip ? 'required' : 'nullable', Rule::in(array_keys(Product::vipColors()))],
            'duration_days' => [$isVip ? 'nullable' : 'required', 'integer', 'min:1', 'max:365'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_key' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['kind'] = $kind;
        $data['daily_income'] = $isVip ? 0 : $data['daily_income'];
        $data['duration_days'] = $isVip ? 30 : $data['duration_days'];
        $data['monthly_salary'] = $isVip ? $data['monthly_salary'] : null;
        $data['member_requirement'] = $isVip ? $data['member_requirement'] : null;
        $data['color'] = $isVip ? $data['color'] : null;
        $data['tagline'] = $data['tagline'] ?? $data['name'];
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['image_key'] = $data['image_key'] ?? null;

        unset($data['image']);

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('locks', 'public');
    }
}
