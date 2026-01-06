// processes a single line
function processLine(str)
{
    return removeOuterMatchingBrackets(str);
}

// removes outer matching brackets from a string
function removeOuterMatchingBrackets(str)
{
    let s = str.trim();

    // continue as long as the string is wrapped in parentheses
    while(s.startsWith('(') && s.endsWith(')'))
    {
        let parenCount = 0;
        let isWrapped = true;

        // loop through the string, exclude last char to detect early closure
        for(let i = 0; i < s.length - 1; i++)
        {
            if (s[i] === '(') parenCount++;
            else if (s[i] === ')') parenCount--;

            // if count reaches zero before the end, the outer brackets are not a matching pair
            if(parenCount === 0)
            {
                isWrapped = false;
                break;
            }
        }

        // if the string is fully enclosed by the outer pair, unwrap it
        if(isWrapped)
        {
            s = s.slice(1, -1).trim();
        } else {
            break;
        }
    }

    return s;
}