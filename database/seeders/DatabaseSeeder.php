<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Bank;
use App\Models\BankUser;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\PointPrice;
use App\Models\Level;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
		$this->genUserSeed();
		$this->genRoleSeed();
		$this->genGameSeed();
		$this->genGameUserSeed();
		$this->genBankSeed();
		$this->genPointPrice();
		$this->genLevel();
    }
	
	public function genUserSeed()
	{
		$data = [
            [
                'username' => 'admin',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 1,
				'level' => 0,
				'level_name' => 'Vip 0',
            ],
            [
                'username' => 'sub_admin',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 1,
				'level' => 0,
				'level_name' => 'Vip 0',
            ],
            [
                'username' => 'agent1',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 2,
				'agent_id' => 2,
				'point_price' => 10000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
                'username' => 'agent2',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 2,
				'agent_id' => 2,
				'point_price' => 12000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
				'agent_id' => 2,
                'username' => 'agent3',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 2,
				'agent_id' => 2,
				'point_price' => 15000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
                'username' => 'member1',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 3,
				'top_id' => 2,
				'agent_id' => 3,
				'point_price' => 50000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
                'username' => 'member2',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 3,
				'top_id' => 2,
				'agent_id' => 3,
				'point_price' => 50000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
                'username' => 'member3',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 3,
				'top_id' => 2,
				'agent_id' => 4,
				'point_price' => 50000,
				'level' => 0,
				'level_name' => 'Vip 0',
			],
            [
                'username' => 'member4',
                'password' => bcrypt('123123'),
				'original_password' => substr(md5(bcrypt('admin')), 0,10),
				'o_password' => '123123',
				'invite_code' => Str::random(7),
				'gender' => 0,
				'is_active' => 1,
				'status' => 1,
				'type_user' => 3,
				'top_id' => 2,
				'agent_id' => 4,
				'point_price' => 50000,
				'level' => 0,
				'level_name' => 'Vip 0',
            ]
        ];

        foreach($data as $item) {
            User::create($item);
        }
	}

	public function genRoleSeed()
	{
		$data = [
            [
                'name' => 'system_admin',
                'guard_name' => 'web',
                'description' => 'System Admin',
            ],
            // [
            //     'name' => 'agent',
            //     'guard_name' => 'web',
            //     'description' => 'Agency',
            // ],
            // [
            //     'name' => 'client',
            //     'guard_name' => 'web',
            //     'description' => 'Client',
			// ],
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Admin',
            ]
        ];

        foreach($data as $item) {
			Role::create($item);
        }

		$menus = config('platform.list_menu');
		foreach ($menus as $key => $menu) {
			foreach ($menu['permission'] as $_key => $_val) {
				$permission_parent = [
					'name' => $_key,
					'pid' => 0,
					'icon' => $menu['icon'],
					'guard_name' => 'web',
					'path' => $menu['path'],
					'level' => $menu['level'],
					'route_name' => $menu['route_name'],
					'status' => $menu['status'],
					'description' => $_val,
					'title_name' => $menu['title_name'],
					'title_description' => $menu['title_description'],
				];

				$per_parent = Permission::create($permission_parent);

				if (isset($menu['child']) && !empty($menu['child'])) {
					foreach ($menu['child'] as $_menu) {
						$permission_child = [
							'name' => $_menu['key_permission'],
							'pid' => $per_parent->id,
							'icon' => $_menu['icon'],
							'guard_name' => 'web',
							'path' => $_menu['path'],
							'level' => $_menu['level'],
							'route_name' => $_menu['route_name'],
							'status' => $_menu['status'],
							'description' => 'Hiển thị',
							'title_name' => $_menu['title_name'],
							'title_description' => $_menu['title_description'],
						];

						$per_child = Permission::create($permission_child);

						foreach ($_menu['permission'] as $_key_child => $_val_child) {
							$permission_child_permission = [
								'name' => $_key_child,
								'pid' => $per_child->id,
								'icon' => $_menu['icon'],
								'guard_name' => 'web',
								'path' => $_menu['path'],
								'level' => 2,
								'route_name' => $_menu['route_name'],
								'status' => $_menu['status'],
								'description' => $_val_child,
								'title_name' => $_menu['title_name'],
								'title_description' => $_menu['title_description'],
							];

							Permission::create($permission_child_permission);
						}
					}
				}
			}
        }

        $role_admin = Role::find(1);
        $role_sub_admin = Role::find(2);
        
        $role_admin->syncPermissions(Permission::all()->pluck('id')->toArray());
        $role_sub_admin->syncPermissions(Permission::all()->pluck('id')->toArray());

		$user = User::find(1);
		$user->assignRole($role_admin);

		$user = User::find(2);
		$user->assignRole($role_sub_admin);
	}

	public function genGameSeed()
	{
		$games = [
			['title' => 'Bong88', 'subtitle' => '', 'web_pic' => '', 'mobile_pic' => '', 'logo_url' => '', 'game_type' => '1', 'params' => '', 'status' => 1, 'client_type' => '1', 'tags' => ''],
			['title' => 'Sbobet', 'subtitle' => '', 'web_pic' => '', 'mobile_pic' => '', 'logo_url' => '', 'game_type' => '1', 'params' => '', 'status' => 1, 'client_type' => '1', 'tags' => ''],
			['title' => 'Lô đề LD789', 'subtitle' => '', 'web_pic' => '', 'mobile_pic' => '', 'logo_url' => '', 'game_type' => '1', 'params' => '', 'status' => 1, 'client_type' => '1', 'tags' => ''],
			['title' => 'Xóc đĩa LVG788', 'subtitle' => '', 'web_pic' => '', 'mobile_pic' => '', 'logo_url' => '', 'game_type' => '1', 'params' => '', 'status' => 1, 'client_type' => '1', 'tags' => ''],
		];

		foreach($games as $game) {
			Game::create($game);
		}
	}

	public function genGameUserSeed()
	{
		$gameUsers = [
            ['user_id' => '4', 'game_id' => 1, 'username' => 'test1','password' => '123123'],
            ['user_id' => '4', 'game_id' => 2, 'username' => 'test1','password' => '123123'],
            ['user_id' => '4', 'game_id' => 3, 'username' => 'test1','password' => '123123'],
            ['user_id' => '4', 'game_id' => 4, 'username' => 'test1','password' => '123123'],
        ];

		foreach($gameUsers as $gameUser) {
			GameUser::create($gameUser);
		}
	}

	public function genBankSeed()
	{
		$data = config('platform.bank_type');

		$sort = 1;
        foreach ($data as $key => $value) {
			Bank::create([
                'key' => $key,
                'name' => $value,
				'sort' => $sort
            ]);

			$sort++;
        }
	}

	public function genPointPrice()
	{
		$data = [
			['title' => '50k', 'point' => 1, 'price' => '50000', 'type_user' => 1],
			['title' => '100k', 'point' => 1, 'price' => '100000', 'type_user' => 1],
			['title' => '10k', 'point' => 1, 'price' => '10000', 'type_user' => 0],
			['title' => '12k', 'point' => 1, 'price' => '12000', 'type_user' => 0],
			['title' => '15k', 'point' => 1, 'price' => '15000', 'type_user' => 0],
			['title' => '20k', 'point' => 1, 'price' => '20000', 'type_user' => 0],
			['title' => '25k', 'point' => 1, 'price' => '25000', 'type_user' => 0],
			['title' => '30k', 'point' => 1, 'price' => '30000', 'type_user' => 0],
			['title' => '50k', 'point' => 1, 'price' => '50000', 'type_user' => 0],
			['title' => '100k', 'point' => 1, 'price' => '100000', 'type_user' => 0],
		];

		foreach($data as $item) {
            PointPrice::create($item);
        }
	}

	public function genLevel()
	{
		$data = [
            [
                'level' => 0,
                'level_name' => 'VIP 0',
                'withdrawal_today' => 1,
                'deposit_money' => 0,
                'level_bonus' => 0,
                'day_bonus' => 0,
                'week_bonus' => 0,
                'month_bonus' => 0,
                'year_bonus' => 0,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 1,
                'level_name' => 'VIP 1',
                'withdrawal_today' => 1,
                'deposit_money' => 500000,
                'level_bonus' => 1,
                'day_bonus' => 0.5,
                'week_bonus' => 1.5,
                'month_bonus' => 8,
                'year_bonus' => 88,
                'credit_bonus' => 50,
                'levelup_type' => 1,
            ],
            [
                'level' => 2,
                'level_name' => 'VIP 2',
                'withdrawal_today' => 2,
                'deposit_money' => 1000000,
                'level_bonus' => 28,
                'day_bonus' => 3,
                'week_bonus' => 5,
                'month_bonus' => 18,
                'year_bonus' => 188,
                'credit_bonus' => 200,
                'levelup_type' => 1,
            ],
            [
                'level' => 3,
                'level_name' => 'VIP 3',
                'withdrawal_today' => 3,
                'deposit_money' => 2000000,
                'level_bonus' => 500000,
                'day_bonus' => 88,
                'week_bonus' => 18,
                'month_bonus' => 58,
                'year_bonus' => 288,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 4,
                'level_name' => 'VIP 4',
                'withdrawal_today' => 4,
                'deposit_money' => 4000000,
                'level_bonus' => 188,
                'day_bonus' => 18,
                'week_bonus' => 38,
                'month_bonus' => 88,
                'year_bonus' => 388,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 5,
                'level_name' => 'VIP 5',
                'withdrawal_today' => 5,
                'deposit_money' => 8000000,
                'level_bonus' => 388,
                'day_bonus' => 38,
                'week_bonus' => 58,
                'month_bonus' => 188,
                'year_bonus' => 588,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 6,
                'level_name' => 'VIP 6',
                'withdrawal_today' => 6,
                'deposit_money' => 10000000,
                'level_bonus' => 888,
                'day_bonus' => 88,
                'week_bonus' => 188,
                'month_bonus' => 388,
                'year_bonus' => 888,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 7,
                'level_name' => 'VIP 7',
                'withdrawal_today' => 7,
                'deposit_money' => 20000000,
                'level_bonus' => 2888,
                'day_bonus' => 188,
                'week_bonus' => 388,
                'month_bonus' => 588,
                'year_bonus' => 1888,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 8,
                'level_name' => 'VIP 8',
                'withdrawal_today' => 8,
                'deposit_money' => 30000000,
                'level_bonus' => 5888,
                'day_bonus' => 388,
                'week_bonus' => 588,
                'month_bonus' => 888,
                'year_bonus' => 3888,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 9,
                'level_name' => 'VIP 9',
                'withdrawal_today' => 9,
                'deposit_money' => 50000000,
                'level_bonus' => 8888,
                'day_bonus' => 688,
                'week_bonus' => 888,
                'month_bonus' => 1888,
                'year_bonus' => 8888.00,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
            [
                'level' => 10,
                'level_name' => 'VIP 10',
                'withdrawal_today' => 10,
                'deposit_money' => 10000000,
                'level_bonus' => 18888,
                'day_bonus' => 888,
                'week_bonus' => 1888,
                'month_bonus' => 3888,
                'year_bonus' => 18888,
                'credit_bonus' => 0,
                'levelup_type' => 1,
            ],
		];

		foreach($data as $item) {
            Level::create($item);
        }
	}
}
