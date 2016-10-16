/**
 * A getter for ${NAME}
 *
#parse("Since.php")
#parse("Author.php")
 *
 * @return ${TYPE_HINT}
 */
public ${STATIC} function get${NAME}()
{
#if (${STATIC} == "static")
    return self::$${FIELD_NAME};
#else
    return $this->${FIELD_NAME};
#end
}
