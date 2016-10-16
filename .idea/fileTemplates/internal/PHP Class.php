<?php
#parse("PHP File Header.php")

#if (${NAMESPACE})
namespace ${NAMESPACE};
#end

#parse("PHP Class Doc Comment.php")
class ${NAME}
{
    /**
     * Constructor to prevent {@link ${NAME}} from being loaded more than once.
     *
     #parse("Since.php")
     #parse("Author.php")
     *
     * @return self
     */
    public function __construct() {
    }
}