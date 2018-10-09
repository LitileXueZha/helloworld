/**
 * Created by 战-不败的象征 on 2017/4/13.
 */

require('../react-15.3.1/build/browser.min');

import { map, takeWhile, forEach } from "iterlib";

getPlayers()
::map(x => x.character())
::takeWhile(x => x.strength > 100)
::forEach(x => console.log(x));