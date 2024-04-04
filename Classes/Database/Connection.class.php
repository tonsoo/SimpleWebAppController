<?php

namespace App\Database;

class Connection {

    private string $ServerSoftware;
    private string $Host;
    private int $Port;
    private string $Database;
    private string $Username;
    private string $Password;

    private \PDO $Connection;

    public function __construct(string $iniFile) {

        if(!file_exists($iniFile)){
            print("Database connection information does not exist");
            return;
        }
        
        $fileSettings = parse_ini_file($iniFile);

        $host = $fileSettings['host'] ?? '';
        $port = $fileSettings['port'] ?? '';
        $software = $fileSettings['software'] ?? '';

        $database = $fileSettings['database'] ?? '';
        $username = $fileSettings['username'] ?? '';
        $password = $fileSettings['password'] ?? '';

        $this->SetServer($host, $port, $software);
        $this->SetInfo($database, $username, $password);
        $this->UpdateConnection();
    }

    public function SetServer(string $host, int $port, string $software) : void {

        $this->Host = $host;
        $this->Port = $port;
        $this->ServerSoftware = $software;
    }

    public function SetInfo(string $database, string $username, string $password) : void {

        $this->Database = $database;
        $this->Username = $username;
        $this->Password = $password;
    }

    private function UpdateConnection() : void {

        $dsn = "{$this->ServerSoftware}:dbname={$this->Database};port={$this->Port};host={$this->Host};";
        try{
            $this->Connection = new \PDO($dsn, $this->Username, $this->Password, [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            
            echo 'Connection successfull!';
        } catch (\PDOException $e){
            print("dsn: {$dsn}. Could not start database connection.\n\t{$e->getMessage()}");
        }
    }
}