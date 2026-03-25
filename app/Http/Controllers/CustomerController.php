<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Table;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request, $table_id = null)
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        session(['tenant_id' => $tenantId]);
        
        $tenant = Tenant::findOrFail($tenantId);

        $tableId = $table_id ?? $request->query('table_id');

        if ($tenant->business_type === 'cafe' && !$tableId) {
            return redirect()->route('customer.tables');
        }
        
        $table = null;
        $activeOrder = null;
        if ($tableId) {
            $table = Table::where('tenant_id', $tenantId)->find($tableId);
            if ($table && $table->status === 'occupied') {
                $activeOrder = Order::with('items')
                    ->where('table_id', $table->id)
                    ->where(function($q) {
                        $q->whereIn('status', ['pending', 'processing'])
                          ->orWhere('status', '')
                          ->orWhereNull('status');
                    })
                    ->latest()
                    ->first();
            }
        }
        
        // Lấy danh mục và sản phẩm đang active thuộc về Quán
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', 1)->orderBy('category_id')->orderBy('id');
        }])->orderBy('sort_order')->orderBy('id')->get();

        $rooms = [];
        if ($tenant->business_type === 'cafe') {
            $rooms = Room::with('tables')
                ->where('tenant_id', $tenantId)
                ->orderBy('sort_order')
                ->get();
        }
        
        return view('customer.order', compact('tenant', 'categories', 'rooms', 'table', 'activeOrder'));
    }

    public function tables_map(Request $request)
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $tenant = Tenant::findOrFail($tenantId);

        // Nếu là mô hình Retail (Bán sỉ/lẻ), chuyển sang trang bán hàng trực tiếp
        if ($tenant->business_type === 'retail') {
            return redirect()->route('customer.order');
        }

        $rooms = Room::with('tables')
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->get();
            
        return view('customer.tables_map', compact('tenant', 'rooms'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        session(['tenant_id' => $tenantId]);

        $items = $request->input('items', []);
        $tableId = $request->input('table_id');

        if (!is_array($items) || empty($items)) {
            return response()->json(['ok' => false, 'msg' => 'Giỏ hàng trống']);
        }

        try {
            DB::beginTransaction();

            $order = null;
            $table = null;
            if ($tableId) {
                $table = Table::find($tableId);
                // Nếu bàn đang bận, tìm đơn hàng hiện có
                if ($table && $table->status === 'occupied') {
                    // Tìm đơn hàng đang chờ hoặc đang xử lý tại bàn này
                    $order = Order::where('table_id', $tableId)
                        ->where(function($q) {
                            $q->whereIn('status', ['pending', 'processing'])
                              ->orWhere('status', '')
                              ->orWhereNull('status');
                        })
                        ->latest()
                        ->first();
                    \Illuminate\Support\Facades\Log::info("F&B: Found existing order for table {$tableId}: " . ($order ? $order->id : 'none'));
                }
            }

            // Nếu chưa có đơn hàng (bàn trống hoặc Retail), tạo đơn mới
            if (!$order) {
                $orderCode = 'HD-' . strtoupper(Str::random(6));
                $order = Order::create([
                    'tenant_id' => $tenantId,
                    'table_id' => $tableId,
                    'order_code' => $orderCode,
                    'total' => 0,
                    'status' => 'pending',
                    'cashier_id' => auth()->id() ?? 1,
                    'note' => $request->input('note', ''),
                    'customer_name' => $request->input('customer_name'),
                    'customer_phone' => $request->input('customer_phone'),
                ]);
                
                if ($table && $table->status !== 'occupied') {
                    $table->update(['status' => 'occupied']);
                }
            } else {
                // Cập nhật thông tin khách hàng nếu có (trong trường hợp đơn hàng đã tồn tại nhưng bổ sung thông tin)
                if ($request->has('customer_name') || $request->has('customer_phone')) {
                    $order->update([
                        'customer_name' => $request->input('customer_name', $order->customer_name),
                        'customer_phone' => $request->input('customer_phone', $order->customer_phone),
                    ]);
                }
            }

            $totalAdd = 0;
            foreach ($items as $it) {
                $p = Product::find($it['id']);
                if (!$p) continue;

                $sub = $p->price * $it['qty'];
                
                // Kiểm tra xem món này đã có trong đơn hàng chưa để gộp
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $p->id)
                    ->first();
                
                if ($orderItem) {
                    $orderItem->increment('qty', $it['qty'], [
                        'subtotal' => $orderItem->subtotal + $sub
                    ]);
                } else {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $p->id,
                        'product_name' => $p->name,
                        'price' => $p->price,
                        'qty' => $it['qty'],
                        'subtotal' => $sub,
                    ]);
                }
                $totalAdd += $sub;
            }

            $order->increment('total', $totalAdd);

            DB::commit();

            return response()->json([
                'ok' => true,
                'order_code' => $order->order_code,
                'msg' => 'Gửi order thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function updateItemQty(Request $request)
    {
        $itemId = $request->input('item_id');
        $change = (int)$request->input('change', 0);
        if (!$itemId) return response()->json(['ok' => false, 'msg' => 'Thiếu ID món']);

        try {
            DB::beginTransaction();
            $item = OrderItem::find($itemId);
            if ($item) {
                $order = $item->order;
                $newQty = $item->qty + $change;
                
                if ($newQty <= 0) {
                    $subtotal = $item->subtotal;
                    $item->delete();
                    $order->decrement('total', $subtotal);
                } else {
                    $oldSub = $item->subtotal;
                    $item->qty = $newQty;
                    $item->subtotal = $item->price * $newQty;
                    $item->save();
                    
                    $diff = $item->subtotal - $oldSub;
                    $order->increment('total', $diff);
                }
            }
            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Đã cập nhật số lượng!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function deleteItem(Request $request)
    {
        $itemId = $request->input('item_id');
        if (!$itemId) return response()->json(['ok' => false, 'msg' => 'Thiếu ID món']);

        try {
            DB::beginTransaction();
            $item = OrderItem::find($itemId);
            if ($item) {
                $order = $item->order;
                $subtotal = $item->subtotal;
                $item->delete();
                
                // Cập nhật lại tổng tiền đơn hàng
                $order->decrement('total', $subtotal);
            }
            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Đã xóa món!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function checkout(Request $request)
    {
        $tableId = $request->input('table_id');
        if (!$tableId) return response()->json(['ok' => false, 'msg' => 'Thiếu ID bàn']);

        try {
            DB::beginTransaction();
            $table = Table::find($tableId);
            if ($table) {
                Order::where('table_id', $tableId)
                    ->whereIn('status', ['pending', 'processing'])
                    ->update(['status' => 'completed']);
                
                $table->update(['status' => 'available']);
            }
            DB::commit();
            return response()->json(['ok' => true, 'msg' => 'Thanh toán thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'table', 'tenant']);
        return view('customer.receipt', compact('order'));
    }
}
