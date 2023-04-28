import {
    LightBulbIcon,
    BriefcaseIcon,
    CalculatorIcon,
    RocketLaunchIcon
} from "@heroicons/vue/24/outline";

import {
    LightBulbIcon as LightBulbIconSolid,
    BriefcaseIcon as BriefcaseIconSolid,
    CalculatorIcon as CalculatorIconSolid,
    RocketLaunchIcon as RocketLaunchIconSolid
} from "@heroicons/vue/24/solid";

export const categoryList = [
    { code: "A", name: "Kebijakan", icon: LightBulbIcon, iconSolid: LightBulbIconSolid },
    { code: "B", name: "Aktifitas", icon: BriefcaseIcon, iconSolid: BriefcaseIconSolid },
    { code: "C", name: "Hasil", icon: CalculatorIcon, iconSolid: CalculatorIconSolid },
    { code: "D", name: "Lain-lain", icon: RocketLaunchIcon, iconSolid: RocketLaunchIconSolid }
];