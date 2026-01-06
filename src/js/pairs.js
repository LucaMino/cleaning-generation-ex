// processes a single line
function processLine(str)
{
    return removeMatchingPairCharacters(str);
}

// removes matching pairs wrapped around the string
function removeMatchingPairCharacters(str)
{
    // set en pairs
    const pairs = ['az', 'by', 'cx', 'dw', 'ev', 'fu', 'gt', 'hs', 'ir', 'jq', 'kp', 'lo', 'mn'];
    // sanitize input
    const s = str.trim();
    // extract first and last characters
    const first = s[0];
    const last = s[s.length - 1];
    const pair = first + last;
    // if pair matches, remove them
    if(pairs.includes(pair))
    {
        return s.slice(1, -1);
    }
    return null;
}