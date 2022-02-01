<?php

namespace Source\Core;

use Source\Support\Message;

/**
 * FSPHP | Class Model Layer Supertype Pattern
 * Query Bilder -> PAra que tenha métodos independentes para poder fazer o filtro da query
 * e ter um acesso muito mais otimizado em todos os modelos que vamos desenvolver
 */
abstract class Model
{
    /** @var object|null */
    protected $data;

    /** @var \PDOException|null */
    protected $fail;

    /** @var Message|null */
    protected $message;

    /** @var string */
    //quem vai montar o queryBilder
    protected $query;

    /** @var string|null */
    //parâmetros para poder filtrar
    protected $params;

    /** @var string */
    //filtrar  a consultar do orderby, organizar os resultados
    protected $order;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var string $entity database table */
    protected static $entity;

    /** @var array $protected no update or create */
    protected static $protected;

    /** @var array $entity database table */
    protected static $required;

    /**
     * Model constructor.
     * @param string $entity database table name
     * @param array $protected table protected columns
     * @param array $required table required columns
     */
    public function __construct(string $entity, array $protected, array $required)
    {
        self::$entity = $entity;
        self::$protected = array_merge($protected, ['created_at', "updated_at"]);
        self::$required = $required;

        $this->message = new Message();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new \stdClass();
        }

        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return ($this->data->$name ?? null);
    }

    /**
     * @return null|object
     */
    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @return \PDOException
     */
    public function fail(): ?\PDOException
    {
        return $this->fail;
    }

    /**
     * @return Message|null
     */
    public function message(): ?Message
    {
        return $this->message;
    }

    // É recomendado neste caso tipar só pelo comentário, já que este método será usado por subclasses
    //Tem que trazer resultados - Com ou sem a condição Where
    /**
     * @param  string|null  $terms
     * @param  string|null  $params
     * @param  string  $columns
     * @return Model|mixed
     */
    /**traz os resultados com ou sem a condição where. */
    public function find(?string $terms = null, ?string $params = null, string $columns = "*")
    {
        if ($terms) {
            $this->query = "SELECT {$columns} FROM ". static::$entity . " WHERE {$terms}";
            //Setar os parâmentos e leva-los para frente. Então não será executado na query, vai salvar na memória para
            //poder utiliza-lo depois
            parse_str($params, $this->params);
            return $this;
        }
        //Se não tiver terms, então traga todos os resultados
        //  static::$entity - a classe filha que vai dizer o nome da tabela
        $this->query = "SELECT {$columns} FROM ". static::$entity;
        return $this;
    }

        /**
     * @param int $id
     * @param string $columns
     * @return null|mixed|Model
     */
    public function findById(int $id, string $columns = "*"): ?Model
    {
        $find =  $this->find("id = :id", "id={$id}", $columns);
        return $find->fetch();
    }

    //BILDER
    /**
     * @param  string  $columnOrder
     *
     * @return $this
     */
    public function order(string $columnOrder): Model
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param  int  $limit
     *
     * @return $this
     */
    public function limit(int $limit): Model
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param  int  $offset
     *
     * @return Model
     */
    public function offset(int $offset): Model
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    //quem vai executar a query e interagir com o banco
    //pd trazer 1 resultado, ou multiplo. A chave serve para escolher qual método quero dentro da execução
    /**
     * @param  bool  $all
     * @return null|array|mixed|Model
     */
    public function fetch(bool $all = false)
    {
        try {
            $stmt = Connect::getInstance()->prepare($this->query.$this->order.$this->limit.$this->offset);
            //Execute pode receber os parâmetros em um array que o mesmo vai fazer a interpretação correta
            $stmt->execute($this->params);

            if (!$stmt->rowCount()) {
                return false;
            }

            //se tiver faz o fetchall, caso contrário, faz o da classe
            if ($all) {
                //resultado do array sendo a própria classe para que possa ser manipulado através do active record
                /*traz array com objs ta msm classe*/
                return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
            }

            //static::class -> a classe que executar o método que vai fazer o fetch
            return $stmt->fetchObject(static::class);
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }


    /*trazer a qtd de resultados
    vai contar os resultados através do ID, tds tabela tem esse campo*/
    /**
     * @param  string  $key
     *
     * @return int
     */
    public function count(string $key = "id"): int
    {
        $stmt = Connect::getInstance()->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    /**
     * @param string $entity
     * @param array $data
     * @return int|null
     */
    protected function create(array $data): ?int
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));

            $stmt = Connect::getInstance()->prepare("INSERT INTO " . static::$entity . " ({$columns}) VALUES ({$values})");
            $stmt->execute($this->filter($data));

            return Connect::getInstance()->lastInsertId();
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    /**
     * @param string $entity
     * @param array $data
     * @param string $terms
     * @param string $params
     * @return int|null
     */
    protected function update(array $data, string $terms, string $params): ?int
    {
        try {
            $dateSet = [];
            foreach ($data as $bind => $value) {
                $dateSet[] = "{$bind} = :{$bind}";
            }
            $dateSet = implode(", ", $dateSet);
            parse_str($params, $params);

            $stmt = Connect::getInstance()->prepare("UPDATE ". static::$entity ." SET {$dateSet} WHERE {$terms}");
            $stmt->execute($this->filter(array_merge($data, $params)));
            return ($stmt->rowCount() ?? 1);
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    /**
     * @param  string  $key
     * @param  string  $value
     *
     * @return bool
     */
    public function delete(string $key, string $value): bool
    {
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM ". static::$entity ." WHERE {$key} = :key");
            $stmt->bindValue("key", $value, \PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    /**
     * @return array|null
     */
    protected function safe(): ?array
    {
        $safe = (array)$this->data;
        foreach (static::$protected as $unset) {
            unset($safe[$unset]);
        }
        return $safe;
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            //FILTER_DEFAULT para passar a responsabilidade de tratar os dados para quem realmente vai estender o modelo
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach (static::$required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}