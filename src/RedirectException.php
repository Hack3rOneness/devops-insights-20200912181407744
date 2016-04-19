<?hh // strict

class RedirectException extends Exception {
    public function __construct(string $msg, private string $page, private bool $success) {
        parent::__construct($msg);
    }

    public function getPage(): string {
        return $this->page;
    }

    public function getSuccess(): bool {
        return $this->success;
    }
}

class RedirectOkException extends RedirectException {
    public function __construct(string $msg, string $page) {
        parent::__construct($msg, $page, true);
    }
}

class RedirectErrorException extends RedirectException {
    public function __construct(string $msg, string $page) {
        parent::__construct($msg, $page, false);
    }
}
