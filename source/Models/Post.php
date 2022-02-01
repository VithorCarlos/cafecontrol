<?php

namespace Source\Models;

use Source\Core\Model;

class Post extends Model
{
    public function __construct()
    {
        parent::__construct("posts", ["id"], ["title", "id", "subtitle", "content"]);   
    }

    public function find(?string $terms = null, ?string $params = null, string $columns = "*" ): Model
    {
        //só vai exibir no blog ou no site aqueles posts que não estão agendados pro futuro
        //evitar trazer posts que n podem ser exibidos no blog
        $terms = "status = :status AND post_at < NOW()" . ($terms ? " AND {$terms}" : "");
        $params = "status=post".($params ? "&{$params}" : "");
        return parent::find($terms, $params, $columns);
    }
    public function findByUri(string $uri, string $columns = "*"): ?Post
    {
       $find = $this->find("uri = :uri", "uri={$uri}", $columns);
       return $find->fetch(); 
    }

    public function author(): ?User
    {
        if ($this->author) {
            return (new User())->findById($this->author);
        }
        return null;
    }

    public function category(): ?Category
    {
        if ($this->category) {
            return (new Category())->findById($this->category);
        }
        return null;
    }

    public function save(): bool
    {
        /** Post Update */
        if (!empty($this->id)) {
            $postId = $this->id;

            $this->update($this->safe(), "id = :id", "id={$postId}");
            if ($this->fail()) {
                $this->message->error("Error ao atualizar, verifique os dados");
                return false;
            }
        }

        /** Post Update */

        //atualizar o active recory
        $this->data = $this->findById($postId)->data();
        return true;
    }
}