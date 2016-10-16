/**
 * A setter for ${NAME}
 *
#parse("Since.php")
#parse("Author.php")
 *
 * @param ${TYPE_HINT} $${PARAM_NAME}
#if (${STATIC} != "static")
 * @return #if (${NAMESPACE})\\${NAMESPACE}\\${CLASS_NAME}#else\\${CLASS_NAME}#end
#else
 * @return void
#end
 */
public ${STATIC} function set${NAME}($${PARAM_NAME})
{
#if (${STATIC} == "static")
    self::$${FIELD_NAME} = $${PARAM_NAME};
#else
    $this->${FIELD_NAME} = $${PARAM_NAME};

    return $this;
#end
}
