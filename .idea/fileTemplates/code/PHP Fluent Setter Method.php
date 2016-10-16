/**
 * A setter for ${NAME}
 *
#parse("Since.php")
#parse("Author.php")
 *
 * @param ${TYPE_HINT} $${PARAM_NAME}
 *
 * @return ${CLASS_NAME}
 */
public function set${NAME}($${PARAM_NAME})
{
    $this->${FIELD_NAME} = $${PARAM_NAME};
    
    return $this;
}
