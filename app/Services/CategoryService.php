<?php
namespace App\Services;

use App\Models\Category;

class CategoryService
{
	public function getCategoryTree($parentId = null,$allCategories = null)
	{
		if(is_null($allCategories)){
			// 从数据库中取出所有类目
			$allCategories = Category::all();
		}

		return $allCategories
			->where('parent_id',$parentId)
			//遍历这些类目，
			->map(function (Category $category) use($allCategories){
				$data = ['id'=>$category->id,'name'=>$category->name];
				//如果当前类目不是父类目，则直接返回
				if(!$category->is_directory){
					return $data;
				}

				//否则递归调用本方法，将返回值放入children字段中
				$data['children'] = $this->getCategoryTree($category->id,$allCategories);
				return $data;
			});
	}
}
