<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

// 支付完成,更新商品销量的监听器类
class UpdateProductSoldCount implements ShouldQueue //  implements ShouldQueue 代表此监听器是异步执行的
{
    

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    // Laravel 会默认执行监听器的 handle 方法，触发的事件会作为 handle 方法的参数
    public function handle(OrderPaid $event)
    {
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();
        // 预加载商品数据
        //load() 方法支持用 . 来加载关联对象的关联对象
        $order->load('items.product');
        // 循环遍历订单的商品
        foreach($order->items as $item){
            $product = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id',$product->id)
                ->whereHas('order',function($query){
                    $query->whereNotNull('paid_at'); // 关联的订单,支付状态是已支付
                })->sum('amount');
            // 更新商品销量、
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }
    }
}
