<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FoldertypeSeeder extends Seeder
{
	public function run()
	{
		$folderTypes = [
			'PERMIT',
			'ECC FILES',
			'CNC FILES',
			'IEE/EIS FILES'
		];

		foreach($folderTypes as $name){
			$exists = $this->db->table('folder_type')
			->where('name', $name)
			->countAllResults();

			if($exists === 0){
				$this->db->table('folder_type')->insert([
					'name' => $name
				]);
			}
		}
	}
}
