# Fuel Data Package.

データのダンパー/ローダーを提供するパッケージです
ダンプされたデータは、出力ディレクトリの中に、コネクション名/テーブル名.csvの形式で出力されます

# Usage

	php oil r data:dump [--dump-dir=/var/tmp/data] [--connections=default,default2...] [--tables=table1,table2...]
	php oil r data:load [--load-dir=/var/tmp/data] [--do-truncate] [--no-truncate]

	--dump-dir
	--load-dir
	出力するディレクトリをフルパスで指定すると、そのディレクトリにダンプ/からロードします
	指定しない場合は、デフォルトで、fuel/app/dataを使用します

	--connections
	使用するコネクション名をカンマ区切りで指定します

	--tables
	ダンプ/ロードしたいテーブルをカンマ区切りで指定します

	--do-truncate
	--no-truncate
	ロードする際に、対象のテーブルをトランケートするか？の指定です
