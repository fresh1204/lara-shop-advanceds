<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $fillable = ['name','is_directory','level','path'];
    protected $cast = [
    	'is_directory' => 'boolean',
    ];

    protected static function boot()
    {
    	parent::boot();
    	// 监听Category的创建事件，用于初始化path和level字段值
    	static::creating(function(Category $category){
    		//如果创建的是一个根目录
    		if(is_null($category->parent_id)){
    			//将层级设为0
    			$category->level = 0;
    			//将path设为-
    			$category->path = '-';
    		}else{
    			//将层级设为父类目的层级+1
    			$category->level = $category->parent->level + 1;
    			//将path设为父类目的path追加父类目ID以及最后跟上一个 - 分隔符
    			$category->path = $category->parent->path.$category->parent_id.'-';
    		}
    	});
    }

    //获取父类目
    public function parent()
    {
    	return $this->belongsTo(Category::class);
    }

    //获取子类目
    public function children()
    {
    	return $this->hasMany(Category::class,'parent_id');
    }

    //关联商品product模型
    public function products()
    {
    	return $this->hasMany(Product::class);
    }

    //定义一个访问器，获取所有祖先类目的ID值
    public function getPathIdsAttribute()
    {	
    	//trim($str,'-')    将字符串两端的-符合去除
    	//array_filter($arr)将数组中的空值移除
    	$arr = explode('-', trim($this->path,'-'));
    	return array_filter($arr);
    }

    //定义一个访问器，获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
    	return Category::query()
    		//获取所有祖先类目ID
    		->whereIn('id',$this->path_ids)
    		//按层级排序
    		->orderBy('level')
    		->get();
    }

    //定义一个访问器，获取以-为分割的所有祖先类目名称以及当前类目名称
    public function getFullNameAttribute()
    {	//echo '<pre>';print_r($this->ancestors->pluck('name')->toArray());echo $this->name;exit;
    	return $this->ancestors //获取所有祖先类目
    				->pluck('name') //取出所有祖先类目的name字段作为一个数组
    				->push($this->name) // 将当前类目的 name 字段值加到数组的末尾
    				->implode(' - '); // 用 - 符号将数组的值组装成一个字符串
    }
}
